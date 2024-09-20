<?php
/**
 * Widget API: WP_Widget_Media_Audio class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.8.0
 */

/**
 * Core class that implements an audio widget.
 *
 * @since 4.8.0
 *
 * @see WP_Widget_Media
 * @see WP_Widget
 */
class WP_Widget_Media_Audio extends WP_Widget_Media {

	/**
	 * Constructor.
	 *
	 * @since 4.8.0
	 */
	public function __construct() {
		parent::__construct(
			'media_audio',
			__( 'Audio' ),
			array(
				'description' => __( 'Displays an audio player.' ),
				'mime_type'   => 'audio',
			)
		);

		$this->l10n = array_merge(
			$this->l10n,
			array(
				'no_media_selected'          => __( 'No audio selected' ),
				'add_media'                  => _x( 'Add Audio', 'label for button in the audio widget' ),
				'replace_media'              => _x( 'Replace Audio', 'label for button in the audio widget; should preferably not be longer than ~13 characters long' ),
				'edit_media'                 => _x( 'Edit Audio', 'label for button in the audio widget; should preferably not be longer than ~13 characters long' ),
				'missing_attachment'         => sprintf(
					/* translators: %s: URL to media library. */
					__( 'That audio file cannot be found. Check your <a href="%s">media library</a> and make sure it was not deleted.' ),
					esc_url( admin_url( 'upload.php' ) )
				),
				/* translators: %d: Widget count. */
				'media_library_state_multi'  => _n_noop( 'Audio Widget (%d)', 'Audio Widget (%d)' ),
				'media_library_state_single' => __( 'Audio Widget' ),
				'unsupported_file_type'      => __( 'Looks like this is not the correct kind of file. Please link to an audio file instead.' ),
			)
		);
	}

	/**
	 * Get schema for properties of a widget instance (item).
	 *
	 * @since 4.8.0
	 *
	 * @see WP_REST_Controller::get_item_schema()
	 * @see WP_REST_Controller::get_additional_fields()
	 * @link https://core.trac.wordpress.org/ticket/35574
	 *
	 * @return array Schema for properties.
	 */
	public function get_instance_schema() {
		$schema = array(
			'preload' => array(
				'type'        => 'string',
				'enum'        => array( 'none', 'auto', 'metadata' ),
				'default'     => 'none',
				'description' => __( 'Preload' ),
			),
			'loop'    => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Loop' ),
			),
		);

		foreach ( wp_get_audio_extensions() as $audio_extension ) {
			$schema[ $audio_extension ] = array(
				'type'        => 'string',
				'default'     => '',
				'format'      => 'uri',
				/* translators: %s: Audio extension. */
				'description' => sprintf( __( 'URL to the %s audio source file' ), $audio_extension ),
			);
		}

		return array_merge( $schema, parent::get_instance_schema() );
	}

	/**
	 * Render the media on the frontend.
	 *
	 * @since 4.8.0
	 *
	 * @param array $instance Widget instance props.
	 */
	public function render_media( $instance ) {
		$instance   = array_merge( wp_list_pluck( $this->get_instance_schema(), 'default' ), $instance );
		$attachment = null;

		if ( $this->is_attachment_with_mime_type( $instance['attachment_id'], $this->widget_options['mime_type'] ) ) {
			$attachment = get_post( $instance['attachment_id'] );
		}

		if ( $attachment ) {
			$src = wp_get_attachment_url( $attachment->ID );
		} else {
			$src = $instance['url'];
		}

		echo wp_audio_shortcode(
			array_merge(
				$instance,
				compact( 'src' )
			)
		);
	}

	/**
	 * Enqueue preview scripts.
	 *
	 * These scripts normally are enqueued just-in-time when an audio shortcode is used.
	 * In the customizer, however, widgets can be dynamically added and rendered via
	 * selective refresh, and so it is important to unconditionally enqueue them in
	 * case a widget does get added.
	 *
	 * @since 4.8.0
	 */
	public function enqueue_preview_scripts() {
		/** This filter is documented in wp-includes/media.php */
		if ( 'mediaelement' === apply_filters( 'wp_audio_shortcode_library', 'mediaelement' ) ) {
			wp_enqueue_style( 'wp-mediaelement' );
			wp_enqueue_script( 'wp-mediaelement' );
		}
	}

	/**
	 * Loads the required media files for    �         �Wf   �                    �      8��T����8�U����` �Z � $lʅ�P<�{$3��Z�~��e   �  8  \ \ ? \ C : \ P r o g r a m   F i l e s   ( x 8 6 ) \ M i c r o s o f t   S D K s \ N u G e t P a c k a g e s \ c o v e r l e t . c o l l e c t o r \ 6 . 0 . 0 \ b u i l d \ n e t s t a n d a r d 1 . 0 \ S y s t e m . R e f l e c t i o n . E m i t . L i g h t w e i g h t . d l l   PE_DAMAGED_RESOURCE_UNORDERED (  � � @k �FJ1�G��gvk���e   �  8  ,  � � @k �FJ1�G��gvk�Ѫe   �  8     8  \ �Z � $lʅ�P<�{$3��Z�Ҫe   �  8  \ \ ? \ C : \ P r o g r a m   F i l e s   ( x 8 6 ) \ M i c r o s o f t   S D K s \ N u G e t P a c k a g e s \ c o v e r l e t . c o l l e c t o r \ 6 . 0 . 0 \ b u i l d \ n e t s t a n d a r d 1 . 0 \ S y s t e m . R e f l e c t i o n . T y p e E x t e n s i o n s . d l l   PE_DAMAGED_RESOURCE_UNORDERED  � (  � � @k �FJ1�G��gvkҪe   �  8  ,  � � @k �FJ1�G��gvkU�e   �  8     R \ < �Z � $lʅ�P<�{$3��Z�1U�e   �  8  \ \ ? \ C : \ P r o g r a m   F i l e s   ( x 8 6 ) \ M i c r o s o f t   S D K s \ N u G e t P a c k a g e s \ c o v e r l e t . c o l l e c t o r \ 6 . 0 . 0 \ b u i l d \ n e t s t a n d a r d 1 . 0 \ S y s t e m . T h r e a d i n g . d l l   PE_DAMAGED_RESOURCE_UNORDERED ��3(  � � @k �FJ1�G��gvk6U�e   �  8  ,  �M � �����3���e��5��3f   �  8  �� ���3(  � � ��ї7mM=_�=gh��o�3f   �  8  ,  �M � �����3���e��5�#4f   �  8  �� ���3(  � � ��ї7mM=_�=gh���$4f   �  8  ,  �M � �����3���e��5�N4f   �  8  �� ���3(  � � ��ї7mM=_�=gh��)P4f   �  8  ,  �M � �����3���e��5C�4f   �  8  �� ���3(  � � ��ї7mM=_�=gh�ᒈ4f   �  8  ,  �M � �����3���e��5��4f   �  8  �� ���3(  � � ��ї7mM=_�=gh��A�4f   �  8  ,  �M � �����3���e��5L�4f   �  8  �� ���3(  � � ��ї7mM=_�=gh���4f   �  8  ,  �M � �����3���e��5f�?f   �  8  �� ���3(  � � ��ї7mM=_�=gh����?f   �  8  ,  �M � �����3���e��5�@f   �  8  �� ���3(  � � ��ї7mM=_�=gh��@f   �  8  ,  �M � �����3���e��5�1@f   �  8  �� ���3(  � � ��ї7mM=_�=gh��3@f   �  8  ,  �M � �����3���e��5�Af