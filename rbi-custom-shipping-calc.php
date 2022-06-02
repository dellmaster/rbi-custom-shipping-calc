<?php
/**
 * Plugin Name: RBI Custom Shipping Calculator
 * Plugin URI: //runbyit.com/
 * Description: Custom Shipping Calculator for WooCommerce
 * Version: 1.0.0
 * Author: Oleksii Yurchenko
 * Author URI: //runbyit.com/
 */

 if ( ! defined( 'WPINC' ) ) {

    die;

}

/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


    function rbi_custom_shipping_method(){

      if ( ! class_exists( 'RBI_Shipping_Method' ) ) {
        require_once 'class-rbi-shipping-method.php';
      }

    }

    add_action( 'woocommerce_shipping_init', 'rbi_custom_shipping_method' );

    add_filter( 'woocommerce_shipping_methods', 'add_rbi_shipping_method' );
    function add_rbi_shipping_method( $methods ) {
        $methods['rbi_shipping'] = 'RBI_Shipping_Method';
        return $methods;
    }

    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //++++++++++++++
    //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    function rbi_validate_order($posted)
    {
      $packages = WC()->shipping->get_packages();
      $chosen_methods = WC()->session->get('chosen_shipping_methods');
      if (is_array($chosen_methods) && in_array('rbi_shipping', $chosen_methods)) {
        foreach ($packages as $i => $package) {
          if ($chosen_methods[$i] != "rbi_shipping") {
          continue;
          }
          $rbi_Shipping_Method = new RBI_Shipping_Method();
          $weightLimit = (int)$rbi_Shipping_Method->settings['weight'];
          $weight = 0;
          foreach ($package['contents'] as $item_id => $values) {
            $_product = $values['data'];
            $weight = $weight + $_product->get_weight() * $values['quantity'];
          }
          $weight = wc_get_weight($weight, 'kg');
          if ($weight > $weightLimit) {
            $message = sprintf(__('OOPS, %d kg increase the maximum weight of %d kg for %s', 'rbi_shipping'), $weight, $weightLimit, $rbi_Shipping_Method->title);
            $messageType = "error";
            if (!wc_has_notice($message, $messageType)) {
            wc_add_notice($message, $messageType);
            }
          }
        }
      }
    }

    //add_action('woocommerce_review_order_before_cart_contents', 'cloudways_validate_order', 10);
    //add_action('woocommerce_after_checkout_validation', 'cloudways_validate_order', 10);

}



add_action( 'admin_menu', 'rbi_shipping_menu_page', 25 );

function rbi_shipping_menu_page(){

	add_menu_page(
		__( 'RBI Shipping Settings', 'rbi_shipping' ), // тайтл страницы
		'RBI Shipping', // текст ссылки в меню
		'manage_options', // права пользователя, необходимые для доступа к странице
		'rbi_shipping_settings', // ярлык страницы
		'rbi_shipping_settings_callback', // функция, которая выводит содержимое страницы
		'dashicons-location', // иконка, в данном случае из Dashicons
		20 // позиция в меню
	);


}

function rbi_shipping_settings_callback(){
  echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';
    submit_button(); // submit button show
		settings_fields( 'rbi_shipping_settings' ); // settings name
		do_settings_sections( 'rbi_shipping_settings_page' ); // page slug
		submit_button(); // submit button show

  echo '</form></div>';
}

add_action( 'admin_init',  'rbi_shipping_fields' );

function rbi_shipping_fields(){

	// options register
	register_setting(
		'rbi_shipping_settings', // setting name from prev step
		'rbi_courier_price', // option slug
		'floatval' // clean function
	);

  register_setting(
		'rbi_shipping_settings', // setting name from prev step
		'rbi_small_pallet_price', // option slug
		'floatval' // функция очистки
	);

  register_setting(
		'rbi_shipping_settings', // setting name from prev step
		'rbi_big_pallet_price', // option slug
		'floatval' // clean function
	);

	// добавляем секцию без заголовка
	add_settings_section(
		'rbi_shipping_settings_section_id', // Section ID we will need it on next step
		__('Basic Shipping Settings', 'rbi_shipping'), // Title
		'', //
		'rbi_shipping_settings_page' // page slug
	);
// END Basic Settings

//START  Advanced Settings
  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_courier_packet_max_weight', // option slug
		'absint' // Clean function
	);

  register_setting(
    'rbi_shipping_settings', // settings name from prev step
    'rbi_courier_packet_max_width', // option slug
    'absint' // Clean function
  );

  register_setting(
    'rbi_shipping_settings', // settings name from prev step
    'rbi_courier_packet_max_height', // option slug
    'absint' // Clean function
  );

  register_setting(
    'rbi_shipping_settings', // settings name from prev step
    'rbi_courier_packet_max_length', // option slug
    'absint' // Clean function
  );

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_small_pallet_max_weight', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_small_pallet_max_width', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_small_pallet_max_height', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_small_pallet_max_length', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_big_pallet_max_weight', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_big_pallet_max_width', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_big_pallet_max_height', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_big_pallet_max_length', // option slug
		'absint' // Clean function
	);

  //Free Shipping settings
  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_free_cat_id', // option slug
		'absint' // Clean function
	);

  register_setting(
		'rbi_shipping_settings', // settings name from prev step
		'rbi_free_min_sum', // option slug
		'absint' // Clean function
	);

  add_settings_section(
		'rbi_shipping_advanced_courier_settings_section_id', // Section ID we will need it on next step
		__('Advanced Courier Shipping Settings', 'rbi_shipping'), // Title
		'', //
		'rbi_shipping_settings_page' // page slug
	);

  add_settings_section(
		'rbi_shipping_advanced_small_pallet_settings_section_id', // Section ID we will need it on next step
		__('Advanced Small Pallet Shipping Settings', 'rbi_shipping'), // Title
		'', //
		'rbi_shipping_settings_page' // page slug
	);

  add_settings_section(
    'rbi_shipping_advanced_big_pallet_settings_section_id', // Section ID we will need it on next step
    __('Advanced Big Pallet Shipping Settings', 'rbi_shipping'), // Title
    '', //
    'rbi_shipping_settings_page' // page slug
  );

  add_settings_section(
    'rbi_shipping_advanced_free_category_settings_section_id', // Section ID we will need it on next step
    __('Free Shipping category settings', 'rbi_shipping'), // Title
    '', //
    'rbi_shipping_settings_page' // page slug
  );
//END  Advanced Settings

	// START Fields for Basic Settings
	add_settings_field(
		'rbi_courier_price',
		__('Transport by courier - Price, PLN', 'rbi_shipping'),
		'rbi_price_field', // display function name
		'rbi_shipping_settings_page', // page lable
		'rbi_shipping_settings_section_id', // section ID
		array(
			'label_for' => 'rbi_courier_price',
			'class' => 'rbisc-tr-class', // for <tr>
			'name' => 'rbi_courier_price', // callback function params
		)
	);

  add_settings_field(
    'rbi_small_pallet_price',
    __('Transport by small pallet - Price, PLN', 'rbi_shipping'),
    'rbi_price_field', // название функции для вывода
    'rbi_shipping_settings_page', // ярлык страницы
    'rbi_shipping_settings_section_id', // // ID секции, куда добавляем опцию
    array(
      'label_for' => 'rbi_small_pallet_price',
      'class' => 'rbisc-tr-class', // для элемента <tr>
      'name' => 'rbi_small_pallet_price', // любые доп параметры в колбэк функцию
    )
  );

  add_settings_field(
    'rbi_big_pallet_price',
    __('Transport by big pallet - Price, PLN', 'rbi_shipping'),
    'rbi_price_field', // название функции для вывода
    'rbi_shipping_settings_page', // ярлык страницы
    'rbi_shipping_settings_section_id', // // ID секции, куда добавляем опцию
    array(
      'label_for' => 'rbi_big_pallet_price',
      'class' => 'rbisc-tr-class', // для элемента <tr>
      'name' => 'rbi_big_pallet_price', // любые доп параметры в колбэк функцию
    )
  );
// END Fields for Basic Settings


//START fields for Advanced Settings

//START Courier Advanced settings
add_settings_field(
  'rbi_courier_packet_max_weight',
  __('Transport by courier - max Weight, kg', 'rbi_shipping'),
  'rbi_weight_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_courier_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_courier_packet_max_weight',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_courier_packet_max_weight', // callback function params
  )
);

add_settings_field(
  'rbi_courier_packet_max_width',
  __('Transport by courier - max Width, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_courier_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_courier_packet_max_width',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_courier_packet_max_width', // callback function params
  )
);

add_settings_field(
  'rbi_courier_packet_max_height',
  __('Transport by courier - max Height, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_courier_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_courier_packet_max_height',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_courier_packet_max_height', // callback function params
  )
);

add_settings_field(
  'rbi_courier_packet_max_length',
  __('Transport by courier - max Length, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_courier_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_courier_packet_max_length',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_courier_packet_max_length', // callback function params
  )
);
//END Courier Advanced settings

// START Small Pallet Advanced settings
add_settings_field(
  'rbi_small_pallet_max_weight',
  __('Transport by small pallet - max Weight, kg', 'rbi_shipping'),
  'rbi_weight_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_small_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_small_pallet_max_weight',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_small_pallet_max_weight', // callback function params
  )
);

add_settings_field(
  'rbi_small_pallet_max_width',
  __('Transport by small pallet - max Width, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_small_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_small_pallet_max_width',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_small_pallet_max_width', // callback function params
  )
);

add_settings_field(
  'rbi_small_pallet_max_height',
  __('Transport by small pallet - max Height, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_small_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_small_pallet_max_height',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_small_pallet_max_height', // callback function params
  )
);

add_settings_field(
  'rbi_small_pallet_max_length',
  __('Transport by small pallet - max Length, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_small_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_small_pallet_max_length',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_small_pallet_max_length', // callback function params
  )
);
// END Small Pallet Advanced settings

//START Big Pallet Advanced settings_fields
add_settings_field(
  'rbi_big_pallet_max_weight',
  __('Transport by big pallet - max Weight, kg', 'rbi_shipping'),
  'rbi_weight_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_big_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_big_pallet_max_weight',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_big_pallet_max_weight', // callback function params
  )
);
add_settings_field(
  'rbi_big_pallet_max_width',
  __('Transport by big pallet - max Width, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_big_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_big_pallet_max_width',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_big_pallet_max_width', // callback function params
  )
);
add_settings_field(
  'rbi_big_pallet_max_height',
  __('Transport by big pallet - max Height, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_big_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_big_pallet_max_height',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_big_pallet_max_height', // callback function params
  )
);
add_settings_field(
  'rbi_big_pallet_max_length',
  __('Transport by big pallet - max Length, mm', 'rbi_shipping'),
  'rbi_size_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_big_pallet_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_big_pallet_max_length',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_big_pallet_max_length', // callback function params
  )
);
//END Big Pallet Advanced settings

// START Free delivery params

add_settings_field(
  'rbi_free_cat_id',
  __('Setup Free Shipping category ID', 'rbi_shipping'),
  'rbi_id_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_free_category_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_free_cat_id',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_free_cat_id', // callback function params
  )
);

add_settings_field(
  'rbi_free_min_sum',
  __('Set Free Shipping minimal Sum', 'rbi_shipping'),
  'rbi_free_sum_field', // display function name
  'rbi_shipping_settings_page', // page lable
  'rbi_shipping_advanced_free_category_settings_section_id', // section ID
  array(
    'label_for' => 'rbi_free_min_sum',
    'class' => 'rbisc-tr-class', // for <tr>
    'name' => 'rbi_free_min_sum', // callback function params
  )
);

// END Free delivery params


  //END fields for Advanced Settings

}

function rbi_price_field( $args ){
	// get value from database table options
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="number" min="1" id="%s" name="%s" value="%d" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		absint( $value )
	);
}

function rbi_weight_field( $args ){
	// get value from database table options
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="number" min="1" id="%s" name="%s" value="%d" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		absint( $value )
	);

}

function rbi_size_field( $args ){
	// get value from database table options
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="number" min="1" id="%s" name="%s" value="%d" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		absint( $value )
	);

}

function rbi_id_field( $args ){
  // get value from database table options
  $value = get_option( $args[ 'name' ] );
  $select_list = '';
  $argscat = array('taxonomy' => 'product_cat');
  $categories = get_categories( $argscat );
  foreach ($categories as $item_cat) {
    $select_list .= '<option value="'.$item_cat->term_id.'">'.$item_cat->name.'</option>';
  }

  printf(
    '<select id="%s" name="%s">'.$select_list.'</select>',
    esc_attr( $args[ 'name' ] ),
    esc_attr( $args[ 'name' ] ),
    absint( $value )
  );

}

function rbi_free_sum_field( $args ){
	// get value from database table options
	$value = get_option( $args[ 'name' ] );

	printf(
		'<input type="number" min="0" id="%s" name="%s" value="%d" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		absint( $value )
	);

}
