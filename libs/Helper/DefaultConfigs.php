<?php

namespace Htsl\Helper;

////////////////////////////////////////////////////////////////

class DefaultConfigs
{
	/**
	 * Getting default configurations of HTSL and Htsl.php
	 *
	 * @access public
	 *
	 * @return array
	 */
	public static function get():array
	{
		return [
			'debug'=> false,
			'charset'=> 'UTF-8',
			'doc_types'=> [
				'HTML5'=>  '<!DOCTYPE html>',
				'XML1'=>   '<?xml version="1.0" encoding="%s"?>',
				'SVG1.1'=> "<?xml version=\"1.0\" standalone=\"no\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">",
			],
			'ENT_flags'=> [
				'HTML5'=>  ENT_HTML5,
				'XML1'=>   ENT_XML1,
				'SVG1.1'=> ENT_XML1,
				'XHTML'=>  ENT_XHTML,
				'HTML4'=>  ENT_HTML401,
			],
			'control_nodes'=> [
				''=> [
					'opener'=> '<?php %s?>',
				],
				'for'=> [
					'opener'=> '<?php $$_FLAG_$=false; for( %s/[^;]*$|;$|;[^ ]/\_/ ): $$_FLAG_$=true;?>',
					'close_by'=> [
						'/else|then/'=> '<?php endfor; if( $$_FLAG_$ ):?>',
					],
					'closer'=> '<?php endfor;?>',
				],
				'while'=> [
					'opener'=> '<?php $$_FLAG_$=false; while( %s ): $$_FLAG_$=true;?>',
					'close_by'=> [
						'/else|then/'=> '<?php endwhile; if( $$_FLAG_$ ):?>',
					],
					'closer'=> '<?php endwhile;?>',
				],
				'do-while'=> [
					'opener'=> '<?php $$_FLAG_$=0; do{ ++$$_FLAG_$;?>',
					'closer'=> '<?php }while( %s );?>',
					'close_by'=> [
						'/else|then/'=> '<?php }while( %s ); if( $$_FLAG_$>1 ):?>',
					],
					'closer'=> '<?php }while( %s );?>',
				],
				'for-each'=> [
					'opener'=> '<?php $$_FLAG_$=false; foreach( %s ): $$_FLAG_$=true;?>',
					'close_by'=> [
						'/else|then/'=> '<?php endforeach; if( $$_FLAG_$ ):?>',
					],
					'closer'=> '<?php endforeach;?>',
				],
				'continue'=> [
					'multiple'=> [
						[
							'pattern'=> '/\)$/',
							'opener'=> '<?php if( %s ) continue;?>',
							'closer'=> '',
						],
						[
							'pattern'=> '/\\w$/',
							'opener'=> '<?php continue;?>',
							'closer'=> '',
						],
					],
				],
				'break'=> [
					'multiple'=> [
						[
							'pattern'=> '/\)$/',
							'opener'=> '<?php if( %s ) break;?>',
							'closer'=> '',
						],
						[
							'pattern'=> '/\\w$/',
							'opener'=> '<?php break;?>',
							'closer'=> '',
						],
					],
				],
				'if'=> [
					'opener'=> '<?php if( %s ):?>',
					'close_by'=> [
						'/else|then/'=> '',
					],
					'closer'=> '<?php endif;?>',
				],
				'if-not'=> [
					'opener'=> '<?php if(!( %s )):?>',
					'close_by'=> [
						'/else|then/'=> '',
					],
					'closer'=> '<?php endif;?>',
				],
				'if-all'=> [
					'opener'=> '<?php if( %s/[^;]*$/\_//^/( //; / )and( //;$/ )/ ):?>',
					'close_by'=> [
						'/else|then/'=> '<?php endif; if( %s/[^;]*$/\_//^/( //; / )or( //;$/ )/ ):?>',
					],
					'closer'=> '<?php endif;?>',
				],
				'if-all-not'=> [
					'opener'=> '<?php if( %s/[^;]*$/\_//^/!( //; / )and!( //;$/ )/ ):?>',
					'close_by'=> [
						'/else|then/'=> '<?php endif; if( %s/[^;]*$/\_//^/!( //; / )or!( //;$/ )/ ):?>',
					],
					'closer'=> '<?php endif;?>',
				],
				'if-not-all-not'=> [
					'opener'=> '<?php if( %s/[^;]*$/\_//^/( //; / )or( //;$/ )/ ):?>',
					'close_by'=> [
						'/else|then/'=> '<?php endif; if( %s/[^;]*$/\_//^/( //; / )and( //;$/ )/ ):?>',
					],
					'closer'=> '<?php endif;?>',
				],
				'if-not-all'=> [
					'opener'=> '<?php if( %s/[^;]*$/\_//^/!( //; / )or!( //;$/ )/ ):?>',
					'close_by'=> [
						'/else|then/'=> '<?php endif; if( %s/[^;]*$/\_//^/!( //; / )and!( //;$/ )/ ):?>',
					],
					'closer'=> '<?php endif;?>',
				],
				'else-if'=> [
					'opener'=> '<?php elseif( %s ):?>',
					'close_by'=> [
						'/else|then/'=> '',
					],
					'closer'=> '<?php endif;?>',
				],
				'else-if-not'=> [
					'opener'=> '<?php elseif(!( %s )):?>',
					'close_by'=> [
						'/else|then/'=> '',
					],
					'closer'=> '<?php endif;?>',
				],
				'else'=> [
					'opener'=> '<?php else:?>',
					'closer'=> '<?php endif;?>',
				],
				'then'=> [
					'opener'=> '',
					'close_by'=> [
						'/else|then/'=> '',
					],
					'closer'=> '<?php endif;?>',
				],
				'switch'=> [
					'opener'=> '<?php switch( %s ):?>',
					'closer'=> '<?php endswitch;?>',
					'scope'=> 'switch',
				],
				'default'=> [
					'in'=> [
						'switch'=> [
							'opener'=> '<?php default:?>',
							'closer'=> '<?php break;?>',
							'scope'=> 'root-default',
						],
						'root-case'=> [
							'opener'=> '<?php default:?>',
							'closer'=> '',
							'scope'=> 'default-in-case',
						],
					],
				],
				'case'=> [
					'in'=> [
						'switch'=> [
							'opener'=> '<?php case %s:?>',
							'closer'=> '<?php break;?>',
							'scope'=> 'root-case',
						],
						'root-case'=> [
							'opener'=> '<?php case %s:?>',
							'closer'=> '',
							'scope'=> 'case-in-case',
						],
						'root-default'=> [
							'opener'=> '<?php case %s:?>',
							'closer'=> '',
							'scope'=> 'case-in-case',
						],
					],
				],
			],
			'tag_nodes'=> [
				'SVG1.1'=> $svgTags= [
					'svg'=> [
						'out'=> [
							'default_attributes'=> ['xmlns'=> 'http://www.w3.org/2000/svg','version'=> '1.1',],
							'params'=> ['viewBox',],
							'scope'=> 'svg',
						],
						'in'=> [
							'svg'=> [
								'params'=> ['x','y','width','height',],
							],
						],
					],
					'*'=> [],
					'polygon'=> [
						'params'=> ['points',],
						'only_in'=> ['svg',],
					],
					'polyline'=> [
						'params'=> ['points',],
						'only_in'=> ['svg',],
					],
					'path'=> [
						'params'=> ['d',],
						'only_in'=> ['svg',],
					],
					'line'=> [
						'params'=> ['x1','y1','x2','y2',],
						'only_in'=> ['svg',],
					],
					'rect'=> [
						'params'=> ['x','y','width','height',],
						'only_in'=> ['svg',],
					],
					'circle'=> [
						'params'=> ['cx','cy','r',],
						'only_in'=> ['svg',],
					],
					'ellipse'=> [
						'params'=> ['cx','cy','rx','ry',],
						'only_in'=> ['svg',],
					],
					'text'=> [
						'params'=> ['x','y',],
						'only_in'=> ['svg',],
					],
				],
				'HTML5'=> [
					'*'=> [],
					''=> [
						'opener'=> '&nbsp;',
						'closer'=> '',
					],
					'charset'=> [
						'name'=> 'meta',
						'params'=> ['charset',],
					],
					'equiv'=> [
						'name'=> 'meta',
						'name_value'=> ['http-equiv','content','scheme',],
					],
					'meta'=> [
						'name'=> 'meta',
						'name_value'=> ['name','content','scheme',],
					],
					'php'=> [
						'opener'=> '<?php ',
						'closer'=> '?>',
						'embedding'=> 'php',
					],
					'code'=> [
						'name'=> 'code',
						'multiple'=> [
							[
								'pattern'=> '/\{>$/',
								'params'=> ['type',],
								'default_attributes'=> ['codeset'=> 'codeset',],
								'embedding'=> 'code',
							],
							[
								'pattern'=> '/.?/',
								'name'=> 'code',
								'params'=> ['type',],
							],
						],
					],
					'js'=> [
						'multiple'=> [
							[
								'pattern'=> '/^-js @/',
								'name'=> 'script',
								'default_attributes'=> ['type'=> 'text/javascript',],
								'link'=> 'src',
							],
							[
								'pattern'=> '/^-js\{>/',
								'name'=> 'script',
								'default_attributes'=> ['type'=> 'text/javascript',],
								'embedding'=> 'js',
							],
						],
					],
					'css'=> [
						'multiple'=> [
							[
								'pattern'=> '/^-css @/',
								'name'=> 'link',
								'default_attributes'=> ['rel'=> 'stylesheet','type'=> 'text/css',],
								'link'=> 'href',
							],
							[
								'pattern'=> '/^-css\{>/',
								'name'=> 'style',
								'default_attributes'=> ['type'=> 'text/css',],
								'embedding'=> 'css',
							],
						],
					],
					'icon'=> [
						'name'=> 'link',
						'default_attributes'=> ['rel'=> 'icon',],
						'params'=> ['sizes',],
						'link'=> 'href',
					],
					'shortcut'=> [
						'name'=> 'link',
						'default_attributes'=> ['rel'=> 'shortcut icon','type'=> 'image/x-icon',],
						'link'=> 'href',
					],
					'link'=> [
						'params'=> ['rel',],
						'link'=> 'href',
					],
					'script'=> [
						'params'=> ['type',],
						'link'=> 'source',
					],
					'a'=> [
						'link'=> 'href',
						'name_value'=> ['name',],
						'target'=> 'target',
					],
					'iframe'=> [
						'link'=> 'src',
						'default_attributes'=> ['frameborder'=> '0',],
						'name_value'=> ['name',],
					],
					'img'=> [
						'link'=> 'src',
						'alt'=> 'alt',
					],
					'fpost'=>   ['name'=> 'form', 'link'=> 'action', 'target'=> 'target', 'name_value'=> ['name',], 'default_attributes'=> ['method'=> 'post',],],
					'fupload'=> ['name'=> 'form', 'link'=> 'action', 'target'=> 'target', 'name_value'=> ['name',], 'default_attributes'=> ['method'=> 'post','enctype'=> 'multipart/form-data',],],
					'fget'=>    ['name'=> 'form', 'link'=> 'action', 'target'=> 'target', 'name_value'=> ['name',], 'default_attributes'=> ['method'=> 'get',],],
					'fput'=>    ['name'=> 'form', 'link'=> 'action', 'target'=> 'target', 'name_value'=> ['name',], 'default_attributes'=> ['method'=> 'put',],],
					'fpatch'=>  ['name'=> 'form', 'link'=> 'action', 'target'=> 'target', 'name_value'=> ['name',], 'default_attributes'=> ['method'=> 'patch',],],
					'fdelete'=> ['name'=> 'form', 'link'=> 'action', 'target'=> 'target', 'name_value'=> ['name',], 'default_attributes'=> ['method'=> 'delete',],],

					'fhidden'=>         ['name'=> 'input',  'default_attributes'=> ['type'=> 'hidden',],         'name_value'=> ['name', 'value', 'form',],],
					'ftext'=>           ['name'=> 'input',  'default_attributes'=> ['type'=> 'text',],           'name_value'=> ['name', 'value', 'form',], 'alt'=> 'placeholder',],
					'fsearch'=>         ['name'=> 'input',  'default_attributes'=> ['type'=> 'search',],         'name_value'=> ['name', 'value', 'form',], 'alt'=> 'placeholder',],
					'fpassword'=>       ['name'=> 'input',  'default_attributes'=> ['type'=> 'password',],       'name_value'=> ['name', 'value', 'form',], 'alt'=> 'placeholder',],
					'femail'=>          ['name'=> 'input',  'default_attributes'=> ['type'=> 'email',],          'name_value'=> ['name', 'value', 'form',], 'alt'=> 'placeholder',],
					'furl'=>            ['name'=> 'input',  'default_attributes'=> ['type'=> 'url',],            'name_value'=> ['name', 'value', 'form',], 'alt'=> 'placeholder',],
					'ftel'=>            ['name'=> 'input',  'default_attributes'=> ['type'=> 'tel',],            'name_value'=> ['name', 'value', 'form',], 'alt'=> 'placeholder',],
					'fnumber'=>         ['name'=> 'input',  'default_attributes'=> ['type'=> 'number',],         'name_value'=> ['name', 'value', 'form',], 'alt'=> 'placeholder', 'params'=> ['min', 'step', 'max',],],
					'frange'=>          ['name'=> 'input',  'default_attributes'=> ['type'=> 'range',],          'name_value'=> ['name', 'value', 'form',],                'params'=> ['min', 'step', 'max',],],
					'fradio'=>          ['name'=> 'input',  'default_attributes'=> ['type'=> 'radio',],          'name_value'=> ['name', 'value', 'form',],],
					'fcheckbox'=>       ['name'=> 'input',  'default_attributes'=> ['type'=> 'checkbox',],       'name_value'=> ['name', 'value', 'form',],],
					'fdate'=>           ['name'=> 'input',  'default_attributes'=> ['type'=> 'date',],           'name_value'=> ['name', 'value', 'form',],],
					'fmonth'=>          ['name'=> 'input',  'default_attributes'=> ['type'=> 'month',],          'name_value'=> ['name', 'value', 'form',],],
					'fweek'=>           ['name'=> 'input',  'default_attributes'=> ['type'=> 'week',],           'name_value'=> ['name', 'value', 'form',],],
					'ftime'=>           ['name'=> 'input',  'default_attributes'=> ['type'=> 'time',],           'name_value'=> ['name', 'value', 'form',],],
					'fdatetime'=>       ['name'=> 'input',  'default_attributes'=> ['type'=> 'datetime',],       'name_value'=> ['name', 'value', 'form',],],
					'fdatetime-local'=> ['name'=> 'input',  'default_attributes'=> ['type'=> 'datetime-local',], 'name_value'=> ['name', 'value', 'form',],],
					'fcolor'=>          ['name'=> 'input',  'default_attributes'=> ['type'=> 'color',],          'name_value'=> ['name', 'value', 'form',],],
					'ffile'=>           ['name'=> 'input',  'default_attributes'=> ['type'=> 'file',],           'name_value'=> ['name', 'form',], 'params'=> ['accept',],],

					'fsubmit'=>         ['name'=> 'button', 'default_attributes'=> ['type'=> 'submit',],         'name_value'=> ['name', 'value','form',], 'link'=> 'formaction', 'target'=> 'formtarget',],
					'freset'=>          ['name'=> 'button', 'default_attributes'=> ['type'=> 'reset',],          'name_value'=> ['form',],],
					'button'=>         ['name'=> 'button', 'default_attributes'=> ['type'=> 'button',],],

					'ftextarea'=>       ['name'=> 'textarea', 'name_value'=> ['name','value','form',],'alt'=> 'placeholder',],

					'fselect'=> [
						'name'=> 'select',
						'name_value'=> ['name', 'value','form',],
						'scope'=> 'select',
					],
					'datalist'=> [
						'params'=> ['id',],
						'scope'=> 'datalist',
					],
					'optgroup'=> [
						'in'=> [
							'select'=> [
								'name_value'=> ['label',],
							],
						],
					],
					'option'=> [
						'in'=> [
							'select'=> [
								'scope_function'=> function( $scope ){
									if( $scope['value']===$this['value'] ){
										$this['selected']= 'selected';
									};
								},
								'name_value'=> ['value',],
								'alt'=> 'label',
							],
							'datalist'=> [
								'name_value'=> ['value',],
							],
						],
					],

					'param'=> [
						'name_value'=> ['name','value',],
					],
					'source'=> [
						'params'=>['type',],
						'link'=> 'src',
					],
					'base'=> [
						'link'=> 'href',
						'target'=> 'target',
					],
					'map'=> [
						'params'=> ['name',],
						'scope'=> 'area-map',
					],
					'area'=> [
						'link'=> 'href',
						'params'=> ['shape','coords'],
						'target'=> 'target',
						'only_in'=> ['area-map',],
					],
					'audio'=> [
						'link'=> 'src',
					],
					'video'=> [
						'link'=> 'src',
					],
					'track'=> [
						'link'=> 'src',
						'param'=> ['kind',],
						'alt'=> 'label',
					],
					'progress'=> [
						'param'=> ['value','max',],
					],
				]+$svgTags,
			],
			'empty_tags'=> [
				'HTML5'=> [
					'br'=>       true,
					'hr'=>       true,
					'img'=>      true,
					'input'=>    true,
					'link'=>     true,
					'meta'=>     true,
					'option'=>   true,
					'param'=>    true,
					'source'=>   true,
					'base'=>     true,
					'area'=>     true,
					'progress'=> true,
				],
				'SVG1.1'=> [
					'polygon'=>  true,
					'polyline'=> true,
					'path'=>     true,
					'line'=>     true,
					'rect'=>     true,
					'circle'=>   true,
					'ellipse'=>  true,
				],
			],
			'indentation'=> [
				'HTML5'=>  false,
				'HTML4'=>  false,
				'XHTML'=>  false,
				'XML1'=>   "\t",
				'SVG1.1'=> "\t",
			],
		];
	}
}
