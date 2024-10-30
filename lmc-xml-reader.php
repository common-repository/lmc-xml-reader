<?php
/*
Plugin Name: LMC XML Reader
Plugin URI: http://wordpress.org/extend/plugins/lmc-xml-reader
Description: Live parses and displays (widget/shortcode) an external XML file from given URL (LMC.cz/Jobs.cz/Prace.cz/Teamio.com).
Version: 1.1
Author: Josef Štěpánek
Author URI: http://josefstepanek.cz


Copyright 2016 Josef Štěpánek (email : josef.stepanek@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/


// Default settings
$instance_default = array(
	'title' => __('', 'lmcxmlreader'),
	'url' => '',
	'limit' => 20,
	'refresh_interval' => 12,
	'show_desc' => true,
	'limit_desc' => 140,
	'lmc_data' => '<p>Probíhá nastavování, zkuste stránku aktualizovat.</p>',
	'id' => '__i__'
);
$related = '';

class WP_Widget_LmcXmlReader extends WP_Widget {


	public function __construct() {
		$widget_ops = array('description' => __( 'Widget, který zobrazuje data z XML od LMC.cz/Jobs.cz/Prace.cz/Teamio.com' ) );
		$control_ops = array('width' => 300);
		parent::__construct('WP_Widget_LmcXmlReader', __('LMC XML Reader'), $widget_ops, $control_ops);
	}


	public function widget($args, $instance) {
		extract($args,EXTR_SKIP);

		$lmc_data = get_transient( $instance['id'] );

		if( $lmc_data === false ) {

			$url_array = preg_split("/\\r\\n|\\r|\\n/", $instance['url']);
			$lmc_data = <<<EOT
				<script>
					document.addEventListener("DOMContentLoaded", function() {
						var related = document.querySelector('#lmc').parentNode.getAttribute('data-related');
						if (related) {
							var positions = document.querySelectorAll('.lmc-div .lmc-item');
							var counter = 0;
							[].forEach.call(positions, function(div) {
								div.style.display = 'none';
								if (div.getAttribute('data-company').indexOf(related) !== -1) {
									div.style.display = 'block';
									counter++;
								}
							});
							document.querySelector('.lmc-div .lmc-noresults').style.display = (counter==0 ? 'block' : 'none');
							try {
								document.getElementById('lmc-select-section').style.display = 'none';
								document.getElementById('lmc-select-region').style.display = 'none';
							} catch (e) { /* No filters */ }
						}
						try {
							document.querySelector('#lmc-select-section').addEventListener('change',function(){
								document.querySelector('.lmc-div.lmc-show').classList.remove('lmc-show');
							    document.querySelector('#lmc-div-'+this.value).classList.add('lmc-show');
							});
							document.querySelector('#lmc-select-region').addEventListener('change',function(){
								var positions = document.querySelectorAll('.lmc-div.lmc-show .lmc-item');
								var region = this.value;
								var counter = 0;
								[].forEach.call(positions, function(div) {
									div.style.display = 'none';
									if (region == '' || div.getAttribute('data-location').indexOf(region) !== -1) {
										div.style.display = 'block';
										counter++;
									}
									if (region == '') {
										counter = 1;
									}
								});
								document.querySelector('.lmc-div.lmc-show .lmc-noresults').style.display = (counter==0 ? 'block' : 'none');
							});
						} catch (e) { /* No filters */ }
						document.getElementById('lmc').classList.add('loaded');
					});
				</script>
EOT;
			$lmc_data .= <<<EOT
				<style type="text/css">
					.lmc-date { float: right; color: inherit; text-decoration: none; }
					.lmc-div { display: none; border-bottom: 1px solid #eee; margin-top: 1.2em; }
					.lmc-show { display: block; }
					.lmc-hidden-limit { display: none; }
					.lmc-desc { margin: 1em 0; }
					.lmc-item { margin-top: 1.5em; }
					.lmc-item::after { display: block; content: ""; border-bottom: 1px solid #eee; margin-top: 1.2em; }
					#lmc select { margin-right: 1em; padding: 0.2em 0.5em; font-size: 115%; width: auto; }
					#lmc { opacity: 0; transition: opacity 0.5s; }
					#lmc.loaded { opacity: 1; }
				</style>
EOT;
			$lmc_data .= '<div id="lmc">'.PHP_EOL;
			$url_index = 0;

			if (count($url_array) > 1) {
				$lmc_data .= '<select id="lmc-select-section">';
				foreach ($url_array as $url) {
					$url = explode(",", $url);
					if (!isset($url[1])) {
						$url[1] = $url[0];
					}
					$lmc_data .= '<option value="'.$url_index.'">'.$url[0].'</option>';
					$url_index++;
				}
				$lmc_data .= '</select>';
				$url_index = 0;

				$lmc_data .= '<select id="lmc-select-region">';
					$lmc_data .= '<option value="">Všechny kraje</option>';
					$lmc_data .= '<option>Praha</option>';
					$lmc_data .= '<option>Středočeský</option>';
					$lmc_data .= '<option>Jihočeský</option>';
					$lmc_data .= '<option>Plzeňský</option>';
					$lmc_data .= '<option>Karlovarský</option>';
					$lmc_data .= '<option>Ústecký</option>';
					$lmc_data .= '<option>Liberecký</option>';
					$lmc_data .= '<option>Královéhradecký</option>';
					$lmc_data .= '<option>Pardubický</option>';
					$lmc_data .= '<option>Olomoucký</option>';
					$lmc_data .= '<option>Moravskoslezský</option>';
					$lmc_data .= '<option>Jihomoravský</option>';
					$lmc_data .= '<option>Zlínský</option>';
					$lmc_data .= '<option>Vysočina</option>';
				$lmc_data .= '</select>';

			}



			foreach ($url_array as $url) {

				$url = explode(",", $url);
				if (!isset($url[1])) {
					$url[1] = $url[0];
				}
				$url[1] = trim($url[1]);
				//$lmc_data = $instance['lmc_data'];
				$ch = curl_init();
				@curl_setopt ($ch, CURLOPT_URL, $url[1]);
				@curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
				@curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
				$contents = curl_exec($ch);
				if (@curl_errno($ch)) {
					$lmc_data .= curl_error($ch);
					$lmc_data .= "\n<br />";
					$contents = '';
				} else {
					@curl_close($ch);
				}

				if (!is_string($contents) || !strlen($contents)) {

					$lmc_data = 'Na zadané URL se nenachází čitelné XML :(<br /><small>Zkontrolujte URL v nastavení LMC XML Reader pluginu.</small>';
					$contents = '';

				} elseif (@simplexml_load_string($contents)) {

					$positionList = new SimpleXMLElement($contents);
					$i = 0;
					$lmc_data .= '<div class="lmc-div'.($url_index==0 ? ' lmc-show' : '').'" id="lmc-div-'.$url_index.'">'.PHP_EOL;
					foreach ($positionList->position as $position) {

						++$i;
						$hidden = '';
						if ($instance['limit'] && $i > $instance['limit']) {
							$hidden = ' lmc-hidden-limit';
						}

						$location = '';
						foreach ($position->localityList->locality as $location_item) {
							if (isset($position->localityList->locality->city)) {
								$location .= $position->localityList->locality->city.' '.$position->localityList->locality->region.' '.$position->localityList->locality->cityPart.' ';
							} else {
								$location .= $location_item.' ';
							}
						}

						$lmc_data .= '<div class="lmc-item '.$hidden.'" id="s_'.$url_index.'_p'.$i.'" data-location="'.$location.'" data-company="'.$position->companyName.'">'.PHP_EOL;
							$lmc_data .= '<a href="#s_'.$url_index.'_p'.$i.'" class="lmc-date"><small>'.date_format(date_create($position->createDate),'j. n. Y, G:i').'</small></a>'.PHP_EOL;
							$lmc_data .= '<h4><a href="'.$position->url.'">'.$position->positionName.'</a></h4>'.PHP_EOL;
							$lmc_data .= '<p class="lmc-desc">'.PHP_EOL;
								$location_echo = ($position->localityList->locality->city ? $position->localityList->locality->city : $position->localityList->locality->region).($position->localityList->locality->cityPart && $position->localityList->locality->cityPart != $position->localityList->locality->city ? ' – '.$position->localityList->locality->cityPart : '');
								if (!isset($position->localityList->locality->city)) {
									$location_echo = $position->localityList->locality[0] . (trim($position->localityList->locality[1]) == '' ? '' : ', '.$position->localityList->locality[1]);
								}
								$lmc_data .= '<strong class="lmc-locality">'.$location_echo.'</strong>'.PHP_EOL;
								$lmc_data .= ' &bull; <strong>'.$position->companyName.'</strong>'.($position->employmentTypeList->employmentType ? ' &bull; <em class="lcm-type">'.$position->employmentTypeList->employmentType.'</em>' : '').PHP_EOL;
								if($instance['show_desc']) {
									$lmc_data .= ' &bull; '.lmcxmlr_getExcerpt($position->teaser,$instance['limit_desc']).PHP_EOL;
								}
							$lmc_data .= '</p>'.PHP_EOL;
						$lmc_data .= '</div>'.PHP_EOL;

					}

					$lmc_data .= '<div class="lmc-div lmc-noresults">Nenalezen žádný výsledek</div>';

					$lmc_data .= '</div>'.PHP_EOL;
					$url_index++;

				} else {
					$lmc_data = 'Na zadané URL se nenachází čitelné XML :(<br /><small>Zkontrolujte URL v nastavení LMC XML Reader pluginu.</small>';
				}

			}


			$lmc_data .= '</div>'.PHP_EOL;
			$lmc_data .= '<!-- LMC XML Reader WordPress Plugin by JosefStepanek.cz -->'.PHP_EOL;
			$lmc_data .= '<!-- XML data last updated: '.date('j. n. Y, G:i:s').' -->'.PHP_EOL;
			$lmc_data .= '<!-- Thanks to Widget Shortcode plugin https://wordpress.org/plugins/widget-shortcode/ -->'.PHP_EOL;


			set_transient( $instance['id'], $lmc_data, $instance['refresh_interval']*60*60 );
		}

		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;
		echo $lmc_data;
		echo $after_widget;
	}


	public function update($new_instance, $old_instance) {
		global $instance_default;
		if( !isset($new_instance['title']) ) // user clicked cancel
				return false;

		$instance = $old_instance;
		$instance['title'] = wp_specialchars( $new_instance['title'] );
		$instance['url'] = wp_specialchars( $new_instance['url'] );
		$instance['limit'] = wp_specialchars( $new_instance['limit'] );
		$instance['show_desc'] = $new_instance['show_desc'];
		$instance['limit_desc'] = wp_specialchars( $new_instance['limit_desc'] );
		$instance['refresh_interval'] = wp_specialchars( $new_instance['refresh_interval'] );
		if(get_transient( $this->id ) === false) {
			$instance['lmc_data'] = wp_specialchars( htmlspecialchars_decode($new_instance['lmc_data']) );
		} else {
			if($this->number != '__i__') {
				$instance['lmc_data'] = get_transient( $this->id );
			}
		}
		$instance['id'] = $this->id;
		delete_transient( $this->id );

		foreach($instance as $opt_name => &$value) { // Set default values to empty options
			if( $value==='' ) $value = $instance_default[$opt_name];
		}

		return $instance;
	}


	public function form($instance) {
		global $instance_default;
		if(!isset($instance['title'])) $instance = $instance_default;

		?>
		<p><label for="<?php echo $this->get_field_id('title') ?>"><?php _e('Nadpis'); ?>:</label>
		<input class="widefat" type="text" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo htmlspecialchars($instance['title'],ENT_QUOTES) ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('url') ?>"><?php _e('Název a URL adresa XML souboru/feedu (jedna na řádek)'); ?>:</label>
		<textarea class="widefat" type="text" rows="6" id="<?php echo $this->get_field_id('url') ?>" name="<?php echo $this->get_field_name('url') ?>"><?php echo htmlspecialchars($instance['url'],ENT_QUOTES) ?></textarea>
		<br /><small class="setting-description"><em>Např. Grafika, http://exporter.lmc.cz/design-portal-grafik.xml</em></small></p>

		<p>
			<label for="<?php echo $this->get_field_id('limit') ?>"><?php _e('Vypsat maximálně '); ?></label>
			<input size="3" type="text" id="<?php echo $this->get_field_id('limit') ?>" name="<?php echo $this->get_field_name('limit') ?>" value="<?php echo htmlspecialchars($instance['limit'],ENT_QUOTES) ?>" /><?php _e(' položek.'); ?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('refresh_interval') ?>"><?php _e('Aktualizovat po '); ?>
				<input size="3" type="text" id="<?php echo $this->get_field_id('refresh_interval') ?>" name="<?php echo $this->get_field_name('refresh_interval') ?>" value="<?php echo htmlspecialchars($instance['refresh_interval'],ENT_QUOTES) ?>" /> hod.
			</label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id('show_desc') ?>" name="<?php echo $this->get_field_name('show_desc') ?>"<?php echo ($instance['show_desc']==true ? ' checked value="true"' : ' value="false"') ?> />
			<label for="<?php echo $this->get_field_id('show_desc') ?>"><?php _e('Zobrazovat popis pozice'); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('limit_desc') ?>"><?php _e('Limit pro popis pozice'); ?>
				<input size="3" type="text" id="<?php echo $this->get_field_id('limit_desc') ?>" name="<?php echo $this->get_field_name('limit_desc') ?>" value="<?php echo htmlspecialchars($instance['limit_desc'],ENT_QUOTES) ?>" /> znaků
			</label>
		</p>

		<textarea style="display:none" id="<?php echo $this->get_field_id('lmc_data') ?>" name="<?php echo $this->get_field_name('lmc_data') ?>"><?php echo htmlspecialchars($instance['lmc_data'],ENT_QUOTES) ?></textarea>

		<input type="hidden" id="<?php echo $this->get_field_id('submit') ?>" name="<?php echo $this->get_field_name('submit') ?>" value="1" />

		<?php if($this->number != '__i__') { ?><p>Data můžete zobrazit také jako shortcode <code>[lmc-xml id="<?php echo $this->id; ?>"]</code> a volitelně přidat parametr <em>related</em> <code>[lmc-xml id="<?php echo $this->id; ?>" related="Název_Společnosti"]</code>.</p><?php } else { echo '<p>Uložte widget pro získání shortcode.</p>'; } ?>

		<?php
	}


} // end class lmcxmlreader




function lmcxmlr_init() {
	register_widget('WP_Widget_LmcXmlReader');
}
add_action('widgets_init', 'lmcxmlr_init');


function lmcxmlr_do_widget( $args ) {
	global $_wp_sidebars_widgets, $wp_registered_widgets, $wp_registered_sidebars, $related;

	if (isset($args['related'])) {
		$related = $args['related'];
	}

	extract( shortcode_atts( array(
		'id' => '',
		'title' => true, /* wheather to display the widget title */
		'before_widget' => '<div id="%1$s" class="widget %2$s" '.($related ? 'data-related="'.$related.'"' : '').'>',
		'before_title' => '<h2 class="widgettitle">',
		'after_title' => '</h2>',
		'after_widget' => '</div>',
		'echo' => true
	), $args, 'widget' ) );


	if( empty( $id ) || ! isset( $wp_registered_widgets[$id] ) )
		return;


	// get the widget instance options
	preg_match( '/(\d+)/', $id, $number );
	$options = get_option( $wp_registered_widgets[$id]['callback'][0]->option_name );
	$instance = $options[$number[0]];
	$class = get_class( $wp_registered_widgets[$id]['callback'][0] );
	$widgets_map = lmcxmlr_get_widgets_map();
	$_original_widget_position = $widgets_map[$id];

	// maybe the widget is removed or de-registered
	if( ! $class )
		return;

	$show_title = ( '0' == $title ) ? false : true;

	/* build the widget args that needs to be filtered through dynamic_sidebar_params */
	$params = array(
		0 => array(
			'name' => $wp_registered_sidebars[$_original_widget_position]['name'],
			'id' => $wp_registered_sidebars[$_original_widget_position]['id'],
			'description' => $wp_registered_sidebars[$_original_widget_position]['description'],
			'before_widget' => $before_widget,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'after_widget' => $after_widget,
			'widget_id' => $id,
			'widget_name' => $wp_registered_widgets[$id]['name'],
			'related' => $related
		),
		1 => array(
			'number' => $number[0]
		)
	);
	$params = apply_filters( 'dynamic_sidebar_params', $params );

	if( ! $show_title ) {
		$params[0]['before_title'] = '<h3 class="widgettitle">';
		$params[0]['after_title'] = '</h3>';
	} elseif( is_string( $title ) && strlen( $title ) > 0 ) {
		$instance['title'] = $title;
	}
	$instance['title'] = '';

	// Substitute HTML id and class attributes into before_widget
	$classname_ = '';
	foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
		if ( is_string( $cn ) )
			$classname_ .= '_' . $cn;
		elseif ( is_object($cn) )
			$classname_ .= '_' . get_class( $cn );
	}
	$classname_ = ltrim( $classname_, '_' );
	$params[0]['before_widget'] = sprintf( $params[0]['before_widget'], $id, $classname_ );

	// render the widget
	ob_start();
	//echo 'COMPANY: '; var_dump($params);
	the_widget( $class, $instance, $params[0] );
	$content = ob_get_clean();

	echo $content;
}


function lmcxmlr_get_widgets_map() {
	$sidebars_widgets = wp_get_sidebars_widgets();
	$widgets_map = array();
	if ( ! empty( $sidebars_widgets ) )
		foreach( $sidebars_widgets as $position => $widgets )
			if( ! empty( $widgets) )
				foreach( $widgets as $widget )
					$widgets_map[$widget] = $position;
	return $widgets_map;
}


function lmcxmlr_shortcode($atts) {
	ob_start();
	lmcxmlr_do_widget($atts);
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
add_shortcode('lmc-xml','lmcxmlr_shortcode');


function lmcxmlr_getExcerpt($str, $maxLength=9999) {
	if(strlen($str) > $maxLength) {
		$startPos = 0;
		$excerpt   = substr($str, $startPos, $maxLength-1);
		$lastSpace = strrpos($excerpt, ' ');
		$excerpt   = substr($excerpt, 0, $lastSpace);
		$excerpt  .= '…';
	} else {
		$excerpt = $str;
	}
	return $excerpt;
}


?>
