<?php
/**
 * A class for displaying various tree-like structures.
 *
 * Extend the Walker class to use it, see examples below. Child classes
 * do not need to implement all of the abstract methods in the class. The child
 * only needs to implement the methods that are needed.
 *
 * @since 2.1.0
 *
 * @package WordPress
 * @abstract
 */
#[AllowDynamicProperties]
class Walker {
	/**
	 * What the class handles.
	 *
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type;

	/**
	 * DB fields to use.
	 *
	 * @since 2.1.0
	 * @var string[]
	 */
	public $db_fields;

	/**
	 * Max number of pages walked by the paged walker.
	 *
	 * @since 2.7.0
	 * @var int
	 */
	public $max_pages = 1;

	/**
	 * Whether the current element has children or not.
	 *
	 * To be used in start_el().
	 *
	 * @since 4.0.0
	 * @var bool
	 */
	public $has_children;

	/**
	 * Starts the list before the elements are added.
	 *
	 * The $args parameter holds additional values that may be used with the child
	 * class methods. This method is called at the start of the output list.
	 *
	 * @since 2.1.0
	 * @abstract
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * The $args parameter holds additional values that may be used with the child
	 * class methods. This method finishes the list at the end of output of the elements.
	 *
	 * @since 2.1.0
	 * @abstract
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param int    $depth  Depth of the item.
	 * @param array  $args   An array of additional arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Starts the element output.
	 *
	 * The $args parameter holds additional values that may be used with the child
	 * class methods. Also includes the element output.
	 *
	 * @since 2.1.0
	 * @since 5.9.0 Renamed `$object` (a PHP reserved keyword) to `$data_object` for PHP 8 named parameter support.
	 * @abstract
	 *
	 * @param string $output            Used to append additional content (passed by reference).
	 * @param object $data_object       The data object.
	 * @param int    $depth             Depth of the item.
	 * @param array  $args              An array of additional arguments.
	 * @param int    $current_object_id Optional. ID of the current item. Default 0.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {}

	/**
	 * Ends the element output, if needed.
	 *
	 * The $args parameter holds additional values that may be used with the child class methods.
	 *
	 * @since 2.1.0
	 * @since 5.9.0 Renamed `$object` (a PHP reserved keyword) to `$data_object` for PHP 8 named parameter support.
	 * @abstract
	 *
	 * @param string $output      Used to append additional content (passed by reference).
	 * @param object $data_object The data object.
	 * @param int    $depth       Depth of the item.
	 * @param array  $args        An array of additional arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = array() ) {}

	/**
	 * Traverses elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth. It is possible to set the
	 * max depth to include all depths, see walk() method.
	 *
	 * This method should not be called directly, use the walk() method instead.
	 *
	 * @since 2.5.0
	 *
	 * @param object $element           Data object.
	 * @param array  $children_elements List of elements to continue traversing (passed by reference).
	 * @param int    $max_depth         Max depth to traverse.
	 * @param int    $depth             Depth of current element.
	 * @param array  $args              An array of arguments.
	 * @param string $output            Used to append additional content (passed by reference).
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element ) {
			return;
		}

		$max_depth = (int) $max_depth;
		$depth     = (int) $depth;

		$id_field = $this->db_fields['id'];
		$id       = $element->$id_field;

		// Display this element.
		$this->has_children = ! empty( $children_elements[ $id ] );
		if ( isset( $args[0] ) && is_array( $args[0] ) ) {
			$args[0]['has_children'] = $this->has_children; // Back-compat.
		}

		$this->start_el( $output, $element, $depth, ...array_values( $args ) );

		// Descend only when the depth is right and there are children for this element.
		if ( ( 0 === $max_depth || $max_depth > $depth + 1 ) && isset( $children_elements[ $id ] ) ) {

			foreach ( $children_elements[ $id ] as $child ) {

				if ( ! isset( $newlevel ) ) {
					$newlevel = true;
					// Start the child delimiter.
					$this->start_lvl( $output, $depth, ...array_values( $args ) );
				}
				$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
			}
			unset( $children_elements[ $id ] );
		}

		if ( isset( $newlevel ) && $newlevel ) {
			// End the child delimiter.
			$this->end_lvl( $output, $depth, ...array_values( $args ) );
		}

		// End this element.
		$this->end_el( $output, $element, $depth, ...array_values( $args ) );
	}

	/**
	 * Displays array of elements hierarchically.
	 *
	 * Does not assume any existing order of elements.
	 *
	 * $max_depth = -1 means flatly display every element.
	 * $max_depth = 0 means display all levels.
	 * $max_depth > 0 specifies the number of display levels.
	 *
	 * @since 2.1.0
	 * @since 5.3.0 Formalized the existing `...$args` parameter by adding it
	 *              to the function signature.
	 *
	 * @param array $elements  An array of elements.
	 * @param int   $max_depth The maximum hierarchical depth.
	 * @param mixed ...$args   Optional additional arguments.
	 * @return string The hierarchical item output.
	 */
	public function walk( $elements, $max_depth, ...$args ) {
		$output = '';

		$max_depth = (int) $max_depth;

		// Invalid parameter or nothing to walk.
		if ( $max_depth < -1 || empty( $elements ) ) {
			return $output;
		}

		$parent_field = $this->db_fields['parent'];

		// Flat display.
		if ( -1 === $max_depth ) {
			$empty_array = array();
			foreach ( $elements as $e ) {
				$this->display_element( $e, $empty_array, 1, 0, $args, $output );
			}
			return $output;
		}

		/*
		 * Need to display in hierarchical order.
		 * Separate elements into two buckets: top level and children elements.
		 * Children_elements is two dimensional array. Example:
		 * Children_elements[10][] contains all sub-elements whose parent is 10.
		 */
		$top_level_elements = array();
		$children_elements  = array();
		foreach ( $elements as $e ) {
			if ( empty( $e->$parent_field ) ) {
				$top_level_elements[] = $e;
			} else {
				$children_elements[ $e->$parent_field ][] = $e;
			}
		}

		/*
		 * When none of the elements is top level.
		 * Assume the first one must be root of the sub elements.
		 */
		if ( empty( $top_level_elements ) ) {

			$first = array_slice( $elements, 0, 1 );
			$root  = $first[0];

			$top_level_elements = array();
			$children_elements  = array();
			foreach ( $elements as $e ) {
				if ( $root->$parent_field === $e->$parent_field ) {
					$top_level_elements[] = $e;
				} else {
					$children_elements[ $e->$parent_field ][] = $e;
				}
			}
		}

		foreach ( $top_level_elements as $e ) {
			$this->display_element( $e, $children_elements, $max_depth, 0, $args, $output );
		}

		/*
		 * If we are displaying all levels, and remaining children_elements is not empty,
		 * then we got orphans, which should be displayed regardless.
		 */
		if ( ( 0 === $max_depth ) && count( $children_elements ) > 0 ) {
			$empty_array = array();
			foreach ( $children_elements as $orphans ) {
				foreach ( $orphans as $op ) {
					$this->display_element( $op, $empt ਤਾਂ ਡਾਉਨਲੋਡ ਫ਼ਾਈਲ ਦੀ ਚੋਣ ਕਰੋ।ਅਸੀਂ ਤੁਹਾਡੀ ਫਾਈਲ ਤੁਰੰਤ ਅਤੇ ਆਸਾਨੀ ਨਾਲ ਦੇਖਣ ਲਈ ਖੋਲ੍ਹੀ ਹੈ। ਜੇਕਰ ਤੁਸੀਂ ਬਾਅਦ ਵਿੱਚ ਇਸਦੀ ਵਰਤੋਂ ਕਰਨਾ ਚਾਹੁੰਦੇ ਹੋ ਤਾਂ ਡਾਉਨਲੋਡ ਫ਼ਾਈਲ ਦੀ ਚੋਣ ਕਰੋ।ਦੇਖਣ ਲਈ ਫ਼ਾਈਲ ਨੂੰ ਖੋਲ੍ਹਣ ਵਿੱਚ ਅਸਮਰੱਥ, ਇਸਦੀ ਬਜਾਏ ਫ਼ਾਈਲ ਨੂੰ ਡਾਉਨਲੋਡ ਕਰ ਰਹੇ ਹਾਂ।ਪ੍ਰਮਾਣੀਕਰਣ ਕੀਤਾ ਜਾ ਰਿਹਾ ਹੈ…ਕਿਰਪਾ ਕਰਕੇ ਉਡੀਕ ਕਰੋ ਜਦੋਂ ਤੱਕ Microsoft Edge ਤੁਹਾਡੇ ਲਈ ਪੁਸ਼ਟੀ ਕਰਦਾ ਹੈਪਛਾਣ…ਪ੍ਰਮਾਣੀਕਰਣ ਸਮਾਪਤ ਹੋ ਗਿਆ।Microsoft Edge ਪਾਸਵਰਡਸ ਨੂੰ ਭਰਨ ਦੀ ਕੋਸ਼ਿਸ਼ ਕਰ ਰਿਹਾ ਹੈ। ਇਸ ਦੀ ਇਜਾਜ਼ਤ ਦੇਣ ਲਈ ਤੁਹਾਡਾ Windows ਪਾਸਵਰਡ ਟਾਈਪ ਕਰੋ।ਆਪਣੀਆਂ ਆਟੋਫਿਲ ਸੈਟਿੰਗਾਂ ਨੂੰ ਅਪਡੇਟ ਕਰਨ ਤੋਂ ਪਹਿਲਾਂ, ਕਿਰਪਾ ਕਰਕੇ ਤਸਦੀਕ ਲਈ ਆਪਣੇ ਡਿਵਾਈਸ ਦਾ ਪਾਸਵਰਡ ਦਾਖਲ ਕਰੋ।Edge ਪ੍ਰਕਿਰਿਆ ਮੌਨੀਟਰMicrosoft Edge ਨੂੰ ਸਥਾਪਿਤ ਕਰਨ ਲਈ QR ਕੋਡMicrosoft Edge ਨੂੰ ਸਥਾਪਿਤ ਕਰਨ ਲਈ ਆਪਣੇ ਮੋਬਾਈਲ ਡਿਵਾਈਸ ਦੇ ਨਾਲ ਇਸ QR ਕੋਡ ਨੂੰ ਸਕੈਨ ਕਰੋਤੁਹਾਡੀਆਂ ਡਿਵਾਇਸ ਵਿਚਕਾਰ ਟੈਬਸ ਭੇਜਣ ਲਈ, ਆਪਣੇ ਮੋਬਾਈਲ ਡਿਵਾਈਸ 'ਤੇ Microsoft Edge ਨੂੰ ਸਥਾਪਿਤ ਕਰੋ ਅਤੇ ਸਾਈਨ ਇਨ ਕਰੋ।Microsoft ਗੋਪਨੀਯਤਾ ਨੀਤੀ{num_people, plural, offset:2
      =1 {{person1} ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ 'ਤੇ ਹੈ}
      =2 {{person1} ਅਤੇ {person2} ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ 'ਤੇ ਹਨ}
      =3 {{person1}, {person2} ਅਤੇ {person3} ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ 'ਤੇ ਹਨ}
      one {{person1}, {person2} ਅਤੇ # ਹੋਰ ਲੋਕ ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ 'ਤੇ ਹਨ}
      other {{person1}, {person2} ਅਤੇ # ਹੋਰ ਲੋਕ ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ 'ਤੇ ਹਨ}}{NUM_PEOPLE, plural, offset:2
      =1 {{person1} ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ ਸਮੂਹ 'ਤੇ ਹੈ}
      =2 {{person1} ਅਤੇ {person2} ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ ਸਮੂਹ 'ਤੇ ਹਨ}
      =3 {{person1}, {person2} ਅਤੇ {person3} ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ ਸਮੂਹ 'ਤੇ ਹਨ}
      one {{person1}, {person2} ਅਤੇ # ਹੋਰ ਵਿਅਕਤੀ ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ ਸਮੂਹ 'ਤੇ ਹਨ}
      other {{person1}, {person2} ਅਤੇ # ਹੋਰ ਲੋਕ ਇਸ ਸਮੇਂ ਇਸ ਟੈਬ ਸਮੂਹ 'ਤੇ ਹਨ}}+$1ਹੋਰ ਸਹਿਯੋਗੀ$1 ਹੋਰ ਸਹਿਯੋਗੀ$1 ਦੁਆਰਾ ਖੋਲ੍ਹਿਆ ਗਿਆ, $2$1 ਦੁਆਰਾ ਖੋਲ੍ਹਿਆ ਗਿਆ$1 ਦੁਆਰਾ ਬਦਲਿਆ ਗਿਆ, $2$1 ਦੁਆਰਾ ਬਦਲਿਆ ਗਿਆ$1 ਦੁਆਰਾ ਲੌਕ ਕੀਤਾ ਗਿਆਤੁਹਾਡੇ ਦੁਆਰਾ ਬਦਲਿਆ ਗਿਆ, $1ਤੁਹਾਡੇ ਦੁਆਰਾ ਖੋਲ੍ਹਿਆ ਗਿਆਤੁਹਾਡੇ ਦੁਆਰਾ ਬਦਲਿਆ ਗਿਆਤੁਹਾਡੇ ਦੁਆਰਾ ਲੌਕ ਕੀਤਾ ਗਿਆਉਹਨਾਂ ਦੀ ਟੈਬ 'ਤੇ ਜਾਓਸੰਪਰਕ ਕਾਰਡ ਖੋਲ੍ਹੋਸੁਨੇਹਾ ਭੇਜੋਇਸ ਸਮੇਂ ਇਸ ਟੈਬ 'ਤੇ$1 ਟੈਬ 'ਤੇ$1 ਚੋਣਾਂਕੰਮਪ੍ਰਦਰਸ਼ਨ ਦੇ ਨਿਸ਼ਾਨਹੀਪ ਸਨੈਪਸ਼ਾਟਹੀਪ ਸੀਮਾਵਾਂਨਮੂਨੇ ਵਾਲੇ ਹੀਪ ਪ੍ਰੋਫਾਈਲਾਂEdge ਵਿੱਚ ਵਿਸ਼ੇਸ਼ ਪੇਸ਼ਕਸ਼ਸਟ੍ਰੀਕ ਨੂੰ ਬ੍ਰਾਉਜ਼ ਕਰਨਾ ਜਾਰੀ ਰੱਖੋਕੁਸ਼ਲਤਾ ਮੋਡ ਨੂੰ ਐਕਸਪਲੋਰ ਕਰੋRobux ਰੀਡੀਮ ਕਰੋOverwatch ਸਿੱਕਿਆਂ ਨੂੰ ਰੀਡੀਮ ਕਰੋRP ਨੂੰ ਰੀਡੀਮ ਕਰੋਗੇਮਿੰਗ ਨੂੰ ਐਕਸਪਲੋਰ ਕਰੋAI ਨੂੰ ਐਕਸਪਲੋਰ ਕਰੋਕੰਮਪ੍ਰਦਰਸ਼ਨ ਵਿੱਚ ਵਾਧਾਦੋਹਰੀ ਖੋਜ ਨਾਲ ਦੋਹਰੇ ਨਤੀਜੇਓਰਕਾ ਵ੍ਹੇਲਸ਼ਨੀ ਗ੍ਰਹਿਅਰੋਰਾ ਬੋਰੇਲਿਸ ਦਾ ਕਾਰਨ ਕੀ ਹੈਤੂਤਨਖਮੁਨ ਕੌਣ ਸੀ?ਬੁਗਾਟੀ ਚਿਰੋਨਵੇਰਨਾਜ਼ਾ ਇਟਲੀਵਿਨਸੈਂਟ ਵੈਨ ਗੋਗਦੁਨੀਆ ਦਾ ਸਭ ਤੋਂ ਉੱਚਾ ਝਰਨਾਫਿਕਸ ਪੌਦਾਨੌਰਥੰਬ�