<?php

namespace Htsl\Parser\Node;

use ArrayAccess;
use Htsl\Parser\Node\Contracts\ANode;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class TagNode extends ANode implements ArrayAccess
{
    /**
     * The html tag name of this node.
     *
     * @var string
     */
    private $tagName;

    /**
     * Whether the html tag is empty.
     *
     * @var bool
     */
    private $isEmpty;

    /**
     * The attributes of this node.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Real constructor.
     *
     * @return \Htsl\Parser\Node\Contracts\ANode
     */
    protected function construct():parent
    {
        $name = $this->line->pregGet('/(?<=^-)[\w-:]+/');
        $this->name = $name;

        $this->loadConfig($name, $this->document);

        $this->tagName = $this->config['name'] ?? $name;
        $this->isEmpty = $this->line->getChar(-1) === '/' || $this->document->getConfig('empty_tags', $this->tagName);
        isset($this->config['default_attributes']) and array_walk($this->config['default_attributes'], function ($value, $key) {
            $this->setAttribute($key, $value);
        });

        return $this;
    }

    /**
     * Opening this tag node, and returning node opener.
     *
     * @return string
     */
    public function open():string
    {
        if (isset($this->config['opener'])) {
            return $this->config['opener'];
        }

        if (isset($this->config['params'])) {
            $this->parseParams();
        }

        if (isset($this->config['name_value'])) {
            $this->parseNameValue();
        }

        if (isset($this->config['link'])) {
            $this->parseLink();
        }

        if (isset($this->config['target'])) {
            $this->parseTarget();
        }

        if (isset($this->config['alt'])) {
            $this->parseAlt();
        }

        $this->parseCommonAttributes();

        if (isset($this->config['in_scope']) && isset($this->config['scope_function']) && is_callable($this->config['scope_function'])) {
            $this->config['scope_function']->call($this, $this->document->scope);
        }

        $finisher = $this->isEmpty ? ' />' : '>';

        return "<{$this->tagName}{$this->attributesString}{$finisher}";
    }

    /**
     * Close this tag node, and returning node closer.
     *
     * @param \Htsl\ReadingBuffer\Line $closerLine The line when node closed.
     *
     * @return string
     */
    public function close(Line $Line):string
    {
        return $this->isEmpty ? '' : $this->config['closer'] ?? "</{$this->tagName}>";
    }

    /**
     * Getting whether this is embedding node and embeding type.
     *
     * @return string
     */
    public function getEmbed():string
    {
        return $this->config['embedding'] ?? '';
    }

    /**
     * Getting whether this node contains a scope and scope name.
     *
     * @return string | null
     */
    public function getScope()
    {
        return $this->config['scope'] ?? null;
    }

    /**
     * Parsing node parameters if needed.
     *
     * @return \Htsl\Parser\Node\TagNode
     */
    protected function parseParams():self
    {
        $params = preg_split('/(?<!\\\\)\\|/', $this->line->pregGet('/^-[\w-:]+\((.*?)\)(?= |(\\{>)?$)/', 1));

        if (($m = count($params)) != ($n = count($this->config['params']))) {
            $this->document->throw("Tag $this->name has $n parameters $m given.");
        }

        array_map(function ($key, $value) {
            return $this->setAttribute($key, str_replace('\\|', '|', $value));
        }, $this->config['params'], $params);

        return $this;
    }

    /**
     * Parsing <name|value> attributes.
     *
     * @return \Htsl\Parser\Node\TagNode
     */
    protected function parseNameValue():self
    {
        $params = $this->line->pregGet('/ <(.*?)>(?= |$)/', 1)
         and $params = preg_split('/(?<!\\\\)\\|/', $params)
          and array_map(function ($key, $value) {
              return isset($key) && isset($value) ? $this->setAttribute($key, $this->checkExpression(str_replace('\\|', '|', $value))) : '';
          }, $this->config['name_value'], $params);

        return $this;
    }

    /**
     * Parsing @links.
     *
     * @return \Htsl\Parser\Node\TagNode
     */
    protected function parseLink():self
    {
        $link = $this->line->pregGet('/ @((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp)?)+?\)))(?= |$)/', 1);

        if (strlen($link)) {
            if (isset($this->config['target']) && ':' === $link[0]) {
                $this->setAttribute($this->config['link'], 'javascript'.$link);
            } elseif ('//' === ($firstTwoLetters = substr($link, 0, 2))) {
                $this->setAttribute($this->config['link'], 'http:'.$link);
            } elseif ('\\\\' === $firstTwoLetters) {
                $this->setAttribute($this->config['link'], 'https://'.substr($link, 2));
            } else {
                $this->setAttribute($this->config['link'], $this->checkExpression($link));
            }
        }

        return $this;
    }

    /**
     * Parsing >target.
     *
     * @return \Htsl\Parser\Node\TagNode
     */
    protected function parseTarget():self
    {
        $target = $this->line->pregGet('/ >((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp)?)+?\)))(?= |$)/', 1);

        if (strlen($target)) {
            $this->setAttribute($this->config['target'], $this->checkExpression($target));
        }

        return $this;
    }

    /**
     * Parsing _placeholders.
     *
     * @return \Htsl\Parser\Node\TagNode
     */
    protected function parseAlt():self
    {
        $alt = $this->line->pregGet('/ _((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp)?)+?\)))(?= |$)/', 1);

        if (strlen($alt)) {
            $this->setAttribute($this->config['alt'], $this->checkExpression($alt));
        }

        return $this;
    }

    /**
     * Parsing #ids .classes ^titles [styles] %event{>listeners<} and {other attributes}.
     *
     * @return \Htsl\Parser\Node\TagNode
     */
    protected function parseCommonAttributes():string
    {
        $attributes = '';

        $id = $this->line->pregGet('/ #([^ ]+|(?<exp>\((?:[^()]+|(?&exp)?)+?\)))(?= |$)/', 1)
         and $this->setAttribute('id', $id);

        $classes = $this->line->pregGet('/ \.[^ ]+(?= |$)/')
         and preg_match_all('/\.((?(?!\()[^.]+|(?<exp>\((?:[^()]+|(?&exp)?)+?\))))/', $classes, $matches)
          and $classes = implode(' ', array_filter(array_map(function ($className) {
              return $this->checkExpression($className);
          }, $matches[1])))
           and $this->setAttribute('class', $classes);

        $title = $this->line->pregGet('/ \^((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp)?)+?\)))(?= |$)/', 1)
         and $this->setAttribute('title', $title);

        $style = $this->line->pregGet('/ \[([^\]]+;)(?=\]( |$))/', 1)
         and $this->setAttribute('style', $style);

        $eventListeners = $this->line->pregMap('/ %(\w+)\{>(.*?)<\}(?= |$)/', function ($string, $name, $code) {
            $this->setAttribute('on'.$name, str_replace('"', '&quot;', $code));
        })
         and implode('', $eventListeners);

        $other = $this->line->pregGet('/(?<=\{).*?(?=;\}( |$))/')
         and array_map(function ($keyValue) {
             preg_replace_callback('/^([\w-:]+)(?:\?(.+?))?(?:\=(.*))?$/', function ($matches) {
                 $this->setAttribute($matches[1], ($matches[3] ?? $matches[1]) ?: $matches[1], $matches[2] ?? null);
             }, $keyValue);
         }, explode(';', $other));

        return $attributes;
    }

    /**
     * Checking and parse PHP expressions.
     *
     * @param string $value
     *
     * @return string
     */
    protected function checkExpression(string $value):string
    {
        return preg_match('/^\(.*\)$/', $value) ? '<?='.substr($value, 1, -1).';?>' : str_replace('"', '&quot;', $value);
    }

    /**
     * Getting attribute string with HTML syntax.
     *
     * @return string
     */
    protected function getAttributesString():string
    {
        ksort($this->attributes);

        return implode('', array_map(static function (string $key, array $data) {
            return isset($data['condition']) && strlen($data['condition']) ?
                "<?php if( {$data['condition']} ){?> $key=\"{$data['value']}\"<?php }?>"
                :
                " $key=\"{$data['value']}\"";
        }, array_keys($this->attributes), $this->attributes));
    }

    /**
     * Setting attribute.
     *
     * @param string      $key       Attribute name.
     * @param string      $value     Attribute value
     * @param string|null $condition Optional condition, If given, attribute will seted only when condition is true.
     */
    protected function setAttribute(string $key, string $value, string $condition = null):self
    {
        if (isset($this->attributes[$key])) {
            $this->document->throw("Attribute $key of $this->name cannot redeclare.");
        }

        $this->attributes[$key] = [
            'value'     => $value,
            'condition' => $condition,
        ];

        return $this;
    }

    /*             *\
       ArrayAccess
    \*             */

    /**
     * Whether the attribute isset.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset):bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Getting attribute with array access.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * Setting Attribute with array access.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset an attribute with array access.
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if (isset($this->attributes[$offset])) {
            unset($this->attributes[$offset]);
        }
    }
}
