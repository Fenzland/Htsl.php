<?php

require __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Htsl\Htsl;

////////////////////////////////////////////////////////////////

class HtslTest extends TestCase
{
	public function __construct( ...$args )
	{
		parent::__construct( ...$args );
		$this->htsl= new Htsl();
	}
	public function testParse()
	{
		$fromStr=  "HTML5\n"
		        .  "-html\n"
		        .  "	-head\n"
		        .  "		-charset(utf-8)\n"
		        .  "		-title\n"
		        .  "			Testing Htsl.php with phpunit\n"
		        .  "	-body\n"
		        .  "		-header\n"
		        .  "			-h1\n"
		        .  "				Title\n"
		        .  "		-main\n"
		        .  "			-section\n"
		        .  "				-post\n"
		        .  "					-text <name> _Pleace input your name\n"
		        .  "					-submit\n"
		        .  "						Submit\n"
		        .  "			-section\n"
		        .  "				-a @\\\\packagist.org/packages/fenzland/htsl\n"
		        .  "					-img @\\\\poser.pugx.org/fenzland/htsl/license\n"
		        .  "				-a @//htsl.fenzland.com\n"
		        .  "					htsl.fenzland.com\n"
		        .  "		-footer\n"
		        .  "			&copy;\n"
		        .  "			-a @mailto:uukoo@163.com\n"
		        .  "				Fenz\n"
		;
		$toStr=    '<!DOCTYPE html>'
		      .    '<html>'
		      .      '<head>'
		      .        '<meta charset="utf-8" />'
		      .        '<title>'
		      .          'Testing Htsl&period;php with phpunit'
		      .        '</title>'
		      .      '</head>'
		      .      '<body>'
		      .        '<header>'
		      .          '<h1>'
		      .            'Title'
		      .          '</h1>'
		      .        '</header>'
		      .        '<main>'
		      .          '<section>'
		      .            '<form method="post">'
		      .              '<input name="name" placeholder="Pleace input your name" type="text" />'
		      .              '<button type="submit">'
		      .                  'Submit'
		      .              '</button>'
		      .            '</form>'
		      .          '</section>'
		      .          '<section>'
		      .            '<a href="https://packagist.org/packages/fenzland/htsl">'
		      .              '<img src="https://poser.pugx.org/fenzland/htsl/license" />'
		      .            '</a>'
		      .            '<a href="http://htsl.fenzland.com">'
		      .              'htsl&period;fenzland&period;com'
		      .            '</a>'
		      .          '</section>'
		      .        '</main>'
		      .        '<footer>'
		      .          '&copy;'
		      .          '<a href="mailto:uukoo@163.com">'
		      .            'Fenz'
		      .          '</a>'
		      .        '</footer>'
		      .      '</body>'
		      .    '</html>'
		;

		$this->assertSame(...[
			$toStr,
			$this->htsl->parse($fromStr),
		]);
	}

	public function testDocType()
	{
		$this->assertSame(  $this->htsl->parse("HTML5\n"),   '<!DOCTYPE html>'                                                                                                                                    );
		$this->assertSame(  $this->htsl->parse("XML1\n"),    "<?xml version=\"1.0\" encoding=\"%s\"?>\n"                                                                                                          );
		$this->assertSame(  $this->htsl->parse("SVG1.1\n"),  "<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n"  );
	}

	public function testHtml5Tags()
	{
		// -charset
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-charset(utf-8)\n"
			),
			'<!DOCTYPE html><meta charset="utf-8" />',
		]);

		// -equiv
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-equiv <foo|bar>\n"
			),
			'<!DOCTYPE html><meta content="bar" http-equiv="foo" />',
		]);

		// -icon
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-icon(16x16) @/foo.svg\n"
			),
			'<!DOCTYPE html><link href="/foo.svg" rel="icon" sizes="16x16" />',
		]);

		// -shortcut
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-shortcut @/bar.ico\n"
			),
			'<!DOCTYPE html><link href="/bar.ico" rel="shortcut icon" type="image/x-icon" />',
		]);

		// -css @
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-css @/foo.css\n"
			),
			'<!DOCTYPE html><link href="/foo.css" rel="stylesheet" type="text/css" />',
		]);

		// -js @
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-js @/foo.js\n"
			),
			'<!DOCTYPE html><script src="/foo.js" type="text/javascript"></script>',
		]);

		// -link
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-link(foo) @/foo.bar\n"
			),
			'<!DOCTYPE html><link href="/foo.bar" rel="foo" />',
		]);

		// -a
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-a <foo> @#foo >_blank\n\tBar\n"
			),
			'<!DOCTYPE html><a href="#foo" name="foo" target="_blank">Bar</a>',
		]);

		// -iframe
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-iframe <foo> @bar\n\tBaz\n"
			),
			'<!DOCTYPE html><iframe frameborder="0" name="foo" src="bar">Baz</iframe>',
		]);

		// -img
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-img @foo _Bar\n"
			),
			'<!DOCTYPE html><img alt="Bar" src="foo" />',
		]);

		// -post
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-post <foo> @bar >_blank\n"
			),
			'<!DOCTYPE html><form action="bar" method="post" name="foo" target="_blank"></form>',
		]);

		// -get
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-get <foo> @bar >_blank\n"
			),
			'<!DOCTYPE html><form action="bar" method="get" name="foo" target="_blank"></form>',
		]);

		// -upload
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-upload <foo> @bar >_blank\n"
			),
			'<!DOCTYPE html><form action="bar" enctype="multipart/form-data" method="post" name="foo" target="_blank"></form>',
		]);

		// -input
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-input <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="hidden" value="bar" />',
		]);

		// -text
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-text <foo|bar|baz> _Place holder\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" placeholder="Place holder" type="text" value="bar" />',
		]);

		// -search
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-search <foo|bar|baz> _Place holder\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" placeholder="Place holder" type="search" value="bar" />',
		]);

		// -password
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-password <foo|bar|baz> _Place holder\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" placeholder="Place holder" type="password" value="bar" />',
		]);

		// -email
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-email <foo|bar|baz> _Place holder\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" placeholder="Place holder" type="email" value="bar" />',
		]);

		// -url
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-url <foo|bar|baz> _Place holder\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" placeholder="Place holder" type="url" value="bar" />',
		]);

		// -tel
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-tel <foo|bar|baz> _Place holder\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" placeholder="Place holder" type="tel" value="bar" />',
		]);

		// -number
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-number(0|1|2) <foo|bar|baz> _Place holder\n"
			),
			'<!DOCTYPE html><input form="baz" max="2" min="0" name="foo" placeholder="Place holder" step="1" type="number" value="bar" />',
		]);

		// -range
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-range(0|1|2) <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" max="2" min="0" name="foo" step="1" type="range" value="bar" />',
		]);

		// -radio
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-radio <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="radio" value="bar" />',
		]);

		// -checkbox
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-checkbox <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="checkbox" value="bar" />',
		]);

		// -date
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-date <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="date" value="bar" />',
		]);

		// -month
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-month <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="month" value="bar" />',
		]);

		// -week
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-week <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="week" value="bar" />',
		]);

		// -time
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-time <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="time" value="bar" />',
		]);

		// -datetime
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-datetime <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="datetime" value="bar" />',
		]);

		// -datetime-local
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-datetime-local <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="datetime-local" value="bar" />',
		]);

		// -color
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-color <foo|bar|baz>\n"
			),
			'<!DOCTYPE html><input form="baz" name="foo" type="color" value="bar" />',
		]);

		// -file
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-file(image/*) <foo|baz>\n"
			),
			'<!DOCTYPE html><input accept="image/*" form="baz" name="foo" type="file" />',
		]);

		// -textarea
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-textarea <foo|bar|baz>\n\tbar\n"
			),
			'<!DOCTYPE html><textarea form="baz" name="foo" value="bar">bar</textarea>',
		]);

		// -select -optgroup -option
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-select <foo|bar|baz>\n\t-optgroup <Option Group>\n\t\t-option <foo|Foo>\n\t\t-option <bar|Bar>\n\t-option <baz|Baz>\n"
			),
			'<!DOCTYPE html><select form="baz" name="foo" value="bar"><optgroup label="Option Group"><option label="Foo" value="foo" /><option label="Bar" selected="selected" value="bar" /></optgroup><option label="Baz" value="baz" /></select>',
		]);

		// -datalist -option
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-datalist(foo)\n\t-option <bar>\n"
			),
			'<!DOCTYPE html><datalist id="foo"><option value="bar" /></datalist>',
		]);

		// -button
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-button\n\tFoo\n"
			),
			'<!DOCTYPE html><button type="button">Foo</button>',
		]);

		// -reset
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-reset <foo>\n\tFoo\n"
			),
			'<!DOCTYPE html><button form="foo" type="reset">Foo</button>',
		]);

		// -submit
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-submit <foo|bar|baz>\n\tFoo\n"
			),
			'<!DOCTYPE html><button form="baz" name="foo" type="submit" value="bar">Foo</button>',
		]);

		// -css embedding
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-css{>\n\t body{display:none;}\n<}\n"
			),
			'<!DOCTYPE html><style type="text/css">body{display:none;}</style>',
		]);

		// -js embedding
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-js{>\n\t x=>x*x;\n<}\n"
			),
			"<!DOCTYPE html><script type=\"text/javascript\">\nx=>x*x;\n</script>",
		]);

		// -php embedding
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-php{>\n\t if( isset(\$bar) )\$foo=\$bar;\n<}\n"
			),
			'<!DOCTYPE html><?php if( isset($bar) )$foo=$bar;?>',
		]);

		// -svg in HTML5
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-svg(0,0,1024,1024)\n"
			),
			'<!DOCTYPE html><svg version="1.1" viewBox="0,0,1024,1024" xmlns="http://www.w3.org/2000/svg"></svg>',
		]);

		// Normal tags
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div\n\tinnerHTML\n"
			),
			'<!DOCTYPE html><div>innerHTML</div>',
		]);

		// Empty tags
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-empty-tag /\n"
			),
			'<!DOCTYPE html><empty-tag />',
		]);


		// Common attributes

		// Tag id
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div #tag-id\n"
			),
			'<!DOCTYPE html><div id="tag-id"></div>',
		]);

		// Tag class
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div .class-1.class-2\n"
			),
			'<!DOCTYPE html><div class="class-1 class-2"></div>',
		]);

		// Tag title
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div ^Tag title\n"
			),
			'<!DOCTYPE html><div title="Tag title"></div>',
		]);

		// Inline styles
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div [width:100px;height:100px;]\n"
			),
			'<!DOCTYPE html><div style="width:100px;height:100px;"></div>',
		]);

		// Other attributes
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div {data-foo=value-foo;data-bar=value-bar;data-baz;}\n"
			),
			'<!DOCTYPE html><div data-bar="value-bar" data-baz="data-baz" data-foo="value-foo"></div>',
		]);

		// Event listener
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div %click{>alert('clicked');<}\n"
			),
			'<!DOCTYPE html><div onclick="alert(\'clicked\');"></div>',
		]);


		// Private

		// Links
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-img @foo.jpg\n-js @foo.js\n-css @foo.css\n-a @foo.html\n-iframe @foo.html\n-post @barring\n-submit @bazzing\n"
			),
			'<!DOCTYPE html><img src="foo.jpg" /><script src="foo.js" type="text/javascript"></script><link href="foo.css" rel="stylesheet" type="text/css" /><a href="foo.html"></a><iframe frameborder="0" src="foo.html"></iframe><form action="barring" method="post"></form><button formaction="bazzing" type="submit"></button>',
		]);

		// Place holders
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-img _Place holder\n-text _Place holder\n-search _Place holder\n-password _Place holder\n-email _Place holder\n-url _Place holder\n-tel _Place holder\n-number(0|1|2) _Place holder\n-textarea _Place holder\n"
			),
			'<!DOCTYPE html><img alt="Place holder" /><input placeholder="Place holder" type="text" /><input placeholder="Place holder" type="search" /><input placeholder="Place holder" type="password" /><input placeholder="Place holder" type="email" /><input placeholder="Place holder" type="url" /><input placeholder="Place holder" type="tel" /><input max="2" min="0" placeholder="Place holder" step="1" type="number" /><textarea placeholder="Place holder"></textarea>',
		]);
	}

	public function testSvg1TagNodes()
	{
		// -svg
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n</svg>\n",
		]);

		// -svg in svg
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-svg(256|256|512|512)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<svg height=\"512\" width=\"512\" x=\"256\" y=\"256\">\n\t</svg>\n</svg>\n",
		]);

		// -polygon
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-polygon(0,0 1,1 2,2)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<polygon points=\"0,0 1,1 2,2\" />\n</svg>\n",
		]);

		// -polyline
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-polyline(0,0 1,1 2,2)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<polyline points=\"0,0 1,1 2,2\" />\n</svg>\n",
		]);

		// -path
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-path(M 243 80 h 80 l -171 640 h -80)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<path d=\"M 243 80 h 80 l -171 640 h -80\" />\n</svg>\n",
		]);

		// -line
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-line(0|0|255|255)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<line x1=\"0\" x2=\"255\" y1=\"0\" y2=\"255\" />\n</svg>\n",
		]);

		// -rect
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-rect(0|0|255|255)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<rect height=\"255\" width=\"255\" x=\"0\" y=\"0\" />\n</svg>\n",
		]);

		// -circle
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-circle(255|255|128)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<circle cx=\"255\" cy=\"255\" r=\"128\" />\n</svg>\n",
		]);

		// -ellipse
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-ellipse(511|511|128|256)\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<ellipse cx=\"511\" cy=\"511\" rx=\"128\" ry=\"256\" />\n</svg>\n",
		]);

		// -text in svg
		$this->assertSame(...[
			$this->htsl->parse(
				"SVG1.1\n-svg(0,0,1024,1024)\n\t-text(511|511)\n\t\tText in SVG\n"
			),
			"<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg version=\"1.1\" viewBox=\"0,0,1024,1024\" xmlns=\"http://www.w3.org/2000/svg\">\n\t<text x=\"511\" y=\"511\">\n\t\tText in SVG\n\t</text>\n</svg>\n",
		]);
	}

	public function testControlNodes()
	{
		// if
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if( \$foo==5 )\n\t-div\n"
			),
			'<!DOCTYPE html><?php if( $foo==5 ):?><div></div><?php endif;?>',
		]);

		// if-not
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if-not( \$foo==5 )\n\t-div\n"
			),
			'<!DOCTYPE html><?php if( !($foo==5) ):?><div></div><?php endif;?>',
		]);

		// if then
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if( \$foo==5 )\n\t-h1\n~then\n\t-h2\n"
			),
			'<!DOCTYPE html><?php if( $foo==5 ):?><h1></h1><h2></h2><?php endif;?>',
		]);

		// if else
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if( \$foo==5 )\n\t-h1\n~else\n\t-h2\n"
			),
			'<!DOCTYPE html><?php if( $foo==5 ):?><h1></h1><?php else:?><h2></h2><?php endif;?>',
		]);

		// if else-if
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if( \$foo==5 )\n\t-h1\n~else-if( \$foo==4 )\n\t-h2\n"
			),
			'<!DOCTYPE html><?php if( $foo==5 ):?><h1></h1><?php elseif( $foo==4 ):?><h2></h2><?php endif;?>',
		]);

		// if-all
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if-all( \$foo==5; \$bar==2; \$baz==1; )\n\t-h1\n"
			),
			'<!DOCTYPE html><?php if( ( $foo==5 )and( $bar==2 )and( $baz==1 ) ):?><h1></h1><?php endif;?>',
		]);

		// if-all then
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if-all( \$foo==5; \$bar==2; \$baz==1; )\n\t-h1\n~then\n\t-h2\n"
			),
			'<!DOCTYPE html><?php if( ( $foo==5 )and( $bar==2 )and( $baz==1 ) ):?><h1></h1><?php endif; if( ( $foo==5 )or( $bar==2 )or( $baz==1 ) ):?><h2></h2><?php endif;?>',
		]);

		// if-all else
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if-all( \$foo==5; \$bar==2; \$baz==1; )\n\t-h1\n~else\n\t-h2\n"
			),
			'<!DOCTYPE html><?php if( ( $foo==5 )and( $bar==2 )and( $baz==1 ) ):?><h1></h1><?php endif; if( ( $foo==5 )or( $bar==2 )or( $baz==1 ) ):?><?php else:?><h2></h2><?php endif;?>',
		]);

		// if-all else-if
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~if-all( \$foo==5; \$bar==2; \$baz==1; )\n\t-h1\n~else-if( \$foo==4 )\n\t-h2\n"
			),
			'<!DOCTYPE html><?php if( ( $foo==5 )and( $bar==2 )and( $baz==1 ) ):?><h1></h1><?php endif; if( ( $foo==5 )or( $bar==2 )or( $baz==1 ) ):?><?php elseif( $foo==4 ):?><h2></h2><?php endif;?>',
		]);

		// switch
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~switch( \$foo )\n\t~default\n\t\t-div\n\t~case( 1 )\n\t\t-h1\n\t~case( 2 )\n\t\t-h2\n"
			),
			'<!DOCTYPE html><?php switch( $foo ):?><?php default:?><div></div><?php break;?><?php case 1:?><h1></h1><?php break;?><?php case 2:?><h2></h2><?php break;?><?php endswitch;?>',
		]);

		// switch case in case and default
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~switch( \$foo )\n\t~default\n\t\t-div\n\t\t~case( 4 )\n\t\t\t-h4\n\t~case( 1 )\n\t\t-h1\n\t\t~case( 3 )\n\t\t-h3\n\t~case( 2 )\n\t\t-h2\n"
			),
			'<!DOCTYPE html><?php switch( $foo ):?><?php default:?><div></div><?php case 4:?><h4></h4><?php break;?><?php case 1:?><h1></h1><?php case 3:?><h3></h3><?php break;?><?php case 2:?><h2></h2><?php break;?><?php endswitch;?>',
		]);

		// switch default in case
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~switch( \$foo )\n\t~case( 1 )\n\t\t-h1\n\t\t~default\n\t\t\t-div\n\t~case( 2 )\n\t\t-h2\n"
			),
			'<!DOCTYPE html><?php switch( $foo ):?><?php case 1:?><h1></h1><?php default:?><div></div><?php break;?><?php case 2:?><h2></h2><?php break;?><?php endswitch;?>',
		]);

		// for
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; for\\( \\$i=0; \\$i<0x10; \\+\\+\\$i \\): \\1=true;\\?><div><\\/div><\\?php endfor;\\?>/',
			$this->htsl->parse(
				"HTML5\n~for( \$i=0; \$i<0x10; ++\$i; )\n\t-div\n"
			),
		]);

		// for then
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; for\\( \\$i=0; \\$i<0x10; \\+\\+\\$i \\): \\1=true;\\?><div><\\/div><\\?php endfor; if\\( \\1 \\):\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~for( \$i=0; \$i<0x10; ++\$i; )\n\t-div\n~then\n\t-footer"
			),
		]);

		// for else
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; for\\( \\$i=0; \\$i<0x10; \\+\\+\\$i \\): \\1=true;\\?><div><\\/div><\\?php endfor; if\\( \\1 \\):\\?><\\?php else:\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~for( \$i=0; \$i<0x10; ++\$i; )\n\t-div\n~else\n\t-footer"
			),
		]);

		// for-each
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; foreach\\( \\$array as \\$key=>\\$value \\): \\1=true;\\?><div><\\/div><\\?php endforeach;\\?>/',
			$this->htsl->parse(
				"HTML5\n~for-each( \$array as \$key=>\$value )\n\t-div\n"
			),
		]);

		// for-each then
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; foreach\\( \\$array as \\$key=>\\$value \\): \\1=true;\\?><div><\\/div><\\?php endforeach; if\\( \\1 \\):\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~for-each( \$array as \$key=>\$value )\n\t-div\n~then\n\t-footer"
			),
		]);

		// for-each else
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; foreach\\( \\$array as \\$key=>\\$value \\): \\1=true;\\?><div><\\/div><\\?php endforeach; if\\( \\1 \\):\\?><\\?php else:\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~for-each( \$array as \$key=>\$value )\n\t-div\n~else\n\t-footer"
			),
		]);

		// while
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; while\\( --\\$i>0 \\): \\1=true;\\?><div><\\/div><\\?php endwhile;\\?>/',
			$this->htsl->parse(
				"HTML5\n~while( --\$i>0 )\n\t-div\n"
			),
		]);

		// while then
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; while\\( --\\$i>0 \\): \\1=true;\\?><div><\\/div><\\?php endwhile; if\\( \\1 \\):\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~while( --\$i>0 )\n\t-div\n~then\n\t-footer"
			),
		]);

		// while else
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=false; while\\( --\\$i>0 \\): \\1=true;\\?><div><\\/div><\\?php endwhile; if\\( \\1 \\):\\?><\\?php else:\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~while( --\$i>0 )\n\t-div\n~else\n\t-footer"
			),
		]);

		// do-while
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=0; do\\{ \\+\\+\\1;\\?><div><\\/div><\\?php \\}while\\( --\\$i>0 \\);\\?>/',
			$this->htsl->parse(
				"HTML5\n~do-while( --\$i>0 )\n\t-div\n"
			),
		]);

		// do-while then
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=0; do\\{ \\+\\+\\1;\\?><div><\\/div><\\?php \\}while\\( --\\$i>0 \\); if\\( \\1>1 \\):\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~do-while( --\$i>0 )\n\t-div\n~then\n\t-footer"
			),
		]);

		// do-while else
		$this->assertRegExp(...[
			'/<!DOCTYPE html><\\?php (\\$\\w+)=0; do\\{ \\+\\+\\1;\\?><div><\\/div><\\?php \\}while\\( --\\$i>0 \\); if\\( \\1>1 \\):\\?><\\?php else:\\?><footer><\\/footer><\\?php endif;\\?>/',
			$this->htsl->parse(
				"HTML5\n~do-while( --\$i>0 )\n\t-div\n~else\n\t-footer"
			),
		]);

		// continue
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~continue\n"
			),
			'<!DOCTYPE html><?php continue;?>',
		]);

		// continue with condition
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~continue( \$foo==5 )\n"
			),
			'<!DOCTYPE html><?php if( $foo==5 ) continue;?>',
		]);

		// break
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~break\n"
			),
			'<!DOCTYPE html><?php break;?>',
		]);

		// break with condition
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n~break( \$foo==5 )\n"
			),
			'<!DOCTYPE html><?php if( $foo==5 ) break;?>',
		]);
	}

	public function testExpressions()
	{
		// Showing expressions
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n=date('Y-m-d')\n"
			),
			'<!DOCTYPE html><?=htmlentities(date(\'Y-m-d\'),'.var_export(ENT_HTML5,true).',\'UTF-8\',false)?>',
		]);

		// Escaped showing expressions
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n`=\$htmlContent\n"
			),
			'<!DOCTYPE html><?=$htmlContent?>',
		]);

		// Tag class expressions
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-div .foo.(\$class).bar\n"
			),
			'<!DOCTYPE html><div class="foo <?=$class?> bar"></div>',
		]);

		// Tag link expressions
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-a @(\$link)\n"
			),
			'<!DOCTYPE html><a href="<?=$link?>"></a>',
		]);

		// Tag target expressions
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-a @:; >(\$target)\n"
			),
			'<!DOCTYPE html><a href="javascript:;" target="<?=$target?>"></a>',
		]);

		// Tag target expressions
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-text _(\$placeholder)\n"
			),
			'<!DOCTYPE html><input placeholder="<?=$placeholder?>" type="text" />',
		]);

		// Tag name-value expressions
		$this->assertSame(...[
			$this->htsl->parse(
				"HTML5\n-input <(\$name)|(\$value)>\n"
			),
			'<!DOCTYPE html><input name="<?=$name?>" type="hidden" value="<?=$value?>" />',
		]);
	}
}
