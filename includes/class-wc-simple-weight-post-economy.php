<?php
class WC_Simple_Weight_Post_Economy_Method extends WC_Shipping_Method {
	/**
	 * Constructor.
	 *
	 * @param int $instance_id
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id = 'wcw_post_economy';
		$this->instance_id = absint( $instance_id );
		$this->method_title = __( 'PostPac Economy', 'wc-simple-weight' );  
		$this->method_description = __( 'PostPac Economy (2 Werktage, inkl. MWST) - Enable this method if you want to offer PostPac Economy as a shipping method', 'wc-simple-weight' ); 
		$this->availability = 'including';
		$this->countries = array( 'CH' );
		$this->init();
		$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
	}
	/**
	 * Initialize custom shiping method.
	 */
	public function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		// Define user set variables
		$this->title = $this->get_option( 'title' );
		$this->postpac_till_2kg = $this->get_option( 'postpac_till_2kg' );
		$this->postpac_till_10kg = $this->get_option( 'postpac_till_10kg' );
		$this->postpac_till_30kg = $this->get_option( 'postpac_till_30kg' );
		$this->weight = $this->get_option( 'weight' );
		// Actions
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}
	/**
	 * Init form fields.
	 */
	function init_form_fields() { 
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable', 'wc-simple-weight' ),
				'type' => 'checkbox',
				'default' => 'no'
			),
			'title' => array(
				'title' => __( 'PostPac Economy (2 Werktage, inkl. MWST)', 'wc-simple-weight' ),
				'type' => 'text',
				'description' => __( 'Title to be displayed on cart and checkout page', 'wc-simple-weight' ),
				'default' => __( 'PostPac Economy (2 Werktage, inkl. MWST)', 'wc-simple-weight' )
			),
			'postpac_till_2kg' => array(
				'title' => __( 'PostPac Economy bis 2 kg', 'wc-simple-weight' ),
				'type' => 'number',
				'default' => 7
			),
			'postpac_till_10kg' => array(
				'title' => __( 'PostPac Economy bis 10 kg', 'wc-simple-weight' ),
				'type' => 'number',
				'default' => 9.7
			),
			'postpac_till_30kg' => array(
				'title' => __( 'PostPac Economy bis 30 kg', 'wc-simple-weight' ),
				'type' => 'number',
				'default' => 20.5
			)
		);
	}
	
	public function calculate_shipping( $package = array() ) {
		$weight = 0;
		$cost = 0;

		foreach ( $package['contents'] as $item_id => $values ) { 
			$_product = $values['data']; 
			$weight = $weight + ($_product->get_weight() * intval($values['quantity'])); 
		}
		
		$weight = wc_get_weight( $weight, 'kg' );
		
		if( $weight <= 2 ) {
			$cost = $this->postpac_till_2kg;
		}
		if( $weight > 2 && $weight <= 10 ) {
			$cost = $this->postpac_till_10kg;
		} 
		if( $weight > 10 && $weight <= 30 ) {
			$cost = $this->postpac_till_30kg;
		}
		
		$this->add_rate( array(
			'id'    => $this->id . $this->instance_id,
			'label' => $this->title . __( 'Weight:', 'wc-simple-weight' ) . ' ' . $weight . ' ' . get_option( 'woocommerce_weight_unit' ) . ')',
			'cost'  => $cost,
		) );

	}
}