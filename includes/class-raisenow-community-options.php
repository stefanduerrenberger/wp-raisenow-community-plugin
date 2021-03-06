<?php


class Raisenow_Community_Options {
	
	/**
	 * a code editor will be instantiated for every item in the array.
	 * each item must contain an other array with the keys 'id' and 'type',
	 * whereas the id refers to the textareas id, that should be replaced with
	 * the editor and the type refers to the mime type of the code.
	 *
	 * @var array
	 */
	private $code_editor_config;
	
	/**
	 * Initialize some custom settings
	 */
	public function init() {
		register_setting( RAISENOW_COMMUNITY_PREFIX . '_donation_settings',
			RAISENOW_COMMUNITY_PREFIX . '_donation_options' );
		
		add_settings_section(
			RAISENOW_COMMUNITY_PREFIX . '_donation_section',
			__( 'Customize donation form', RAISENOW_COMMUNITY_PREFIX, 'raisenow-community' ),
			[ &$this, 'donation_options_section_header' ],
			RAISENOW_COMMUNITY_PREFIX . '_donation_settings'
		);
		
		add_settings_field(
			RAISENOW_COMMUNITY_PREFIX . '_javascript',
			__( 'Add custom script', RAISENOW_COMMUNITY_PREFIX, 'raisenow-community' ),
			[ &$this, 'render_custom_code_option' ],
			RAISENOW_COMMUNITY_PREFIX . '_donation_settings',
			RAISENOW_COMMUNITY_PREFIX . '_donation_section',
			[
				'option_id' => 'javascript',
				'helptext'  => "<p>" . __( 'Enter your javascript below. It will be applied to all donation forms.',
						RAISENOW_COMMUNITY_PREFIX, 'raisenow-community' ) . "</p>",
			]
		);
		
		$this->add_code_editor_config( RAISENOW_COMMUNITY_PREFIX . '_donation_options-javascript', 'text/javascript' );
		
		add_settings_field(
			RAISENOW_COMMUNITY_PREFIX . '_css',
			__( 'Add custom css', RAISENOW_COMMUNITY_PREFIX, 'raisenow-community' ),
			[ &$this, 'render_custom_code_option' ],
			RAISENOW_COMMUNITY_PREFIX . '_donation_settings',
			RAISENOW_COMMUNITY_PREFIX . '_donation_section',
			[
				'option_id' => 'css',
				'helptext'  => "<p>" . __( 'Enter your custom css below. It will be applied to all donation forms.',
						RAISENOW_COMMUNITY_PREFIX, 'raisenow-community' ) . "</p>",
			]
		);
		
		$this->add_code_editor_config( RAISENOW_COMMUNITY_PREFIX . '_donation_options-css', 'text/css' );
	}
	
	public function donation_options_section_header() {
		echo __( 'Use the options below to customize your donation form.', RAISENOW_COMMUNITY_PREFIX, 'raisenow-community' );
	}
	
	public function render_custom_code_option( $args ) {
		$options_id = RAISENOW_COMMUNITY_PREFIX . '_donation_options';
		$options    = get_option( $options_id );
		
		if ( isset( $options[ $args['option_id'] ] ) ) {
			$input = $options[ $args['option_id'] ];
		} else {
			$input = '';
		}
		
		echo $args['helptext'];
		echo "<textarea style='resize: both;' name='{$options_id}[{$args['option_id']}]' id='$options_id-{$args['option_id']}'>$input</textarea>";
	}
	
	/**
	 * Add another code editor instance configuration
	 *
	 * @param string $id the id of the textarea that will be replaced with the code editor
	 * @param string $type the MIME type of the code
	 */
	private function add_code_editor_config( $id, $type ) {
		$this->code_editor_config[] = array(
			'id'   => $id,
			'type' => $type
		);
	}
	
	public function add_code_editor() {
		foreach ( $this->code_editor_config as $config ) {
			// Enqueue code editor and settings for manipulating script.
			$settings = wp_enqueue_code_editor( array( 'type' => $config['type'] ) );
			
			// Bail if user disabled CodeMirror.
			if ( false === $settings ) {
				return;
			}
			
			wp_add_inline_script(
				'code-editor',
				sprintf(
					"jQuery( function() { wp.codeEditor.initialize( '{$config['id']}', %s ); } );",
					wp_json_encode( $settings )
				)
			);
		}
	}
}