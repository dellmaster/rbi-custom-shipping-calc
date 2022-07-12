<?php
class RBI_Shipping_Method extends WC_Shipping_Method {
    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */
    public function __construct( ) {
        $this->id                 = 'rbi_shipping';
        /*$this->instance_id           = absint( $instance_id );*/
        $this->method_title       = __( 'Combined Shipping', 'rbi_shipping' );
        $this->method_description = __( 'Custom Shipping Method for RBI', 'rbi_shipping' );
        /*$this->supports              = array(
    			'shipping-zones',
    			'instance-settings',
    		);*/
        /*$this->availability = 'including';
        $this->countries = array(
          'PL', // Poland
          'Poland',
          'UA',
          'Ukraine',
        );*/

        $this->init();

        $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
        $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Combined Shipping', 'rbi_shipping' );

        // Save settings in admin if you have any defined
        //add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

        //START Price Settings
        $this->courier_price = ( null !== get_option('rbi_courier_price') ) ? get_option('rbi_courier_price') : 0;
        $this->small_pallet_price = ( null !== get_option('rbi_small_pallet_price') ) ? get_option('rbi_small_pallet_price') : 0;
        $this->big_pallet_price = ( null !== get_option('rbi_big_pallet_price') ) ? get_option('rbi_big_pallet_price') : 0;
        //END Price Settings

        //START Weight Settings

        $this->courier_max_weight = ( null !== get_option('rbi_courier_packet_max_weight') ) ? floatval(get_option('rbi_courier_packet_max_weight')) : 0;
        $this->small_pallet_max_weight = (null !== get_option('rbi_small_pallet_max_weight') ) ? floatval(get_option('rbi_small_pallet_max_weight')) : 0;
        $this->big_pallet_max_weight = ( null !== get_option('rbi_big_pallet_max_weight') ) ? floatval(get_option('rbi_big_pallet_max_weight')) : 0;
        //END Weight Settings

        //START Size Settings

        $this->courier_max_width = ( null !== get_option('rbi_courier_packet_max_width') ) ? get_option('rbi_courier_packet_max_width') : 0;
        $this->courier_max_height = ( null !== get_option('rbi_courier_packet_max_height') ) ? get_option('rbi_courier_packet_max_height') : 0;
        $this->courier_max_length = ( null !== get_option('rbi_courier_packet_max_length') ) ? get_option('rbi_courier_packet_max_length') : 0;


        $this->small_pallet_max_width = ( null !== get_option('rbi_small_pallet_max_width') ) ? get_option('rbi_small_pallet_max_width') : 0;
        $this->small_pallet_max_height = ( null !== get_option('rbi_small_pallet_max_height') ) ? get_option('rbi_small_pallet_max_height') : 0;
        $this->small_pallet_max_length = ( null !== get_option('rbi_small_pallet_max_length') ) ? get_option('rbi_small_pallet_max_length') : 0;



        $this->big_pallet_max_width = ( null !== get_option('rbi_big_pallet_max_width') ) ? get_option('rbi_big_pallet_max_width') : 0;
        $this->big_pallet_max_height = ( null !== get_option('rbi_big_pallet_max_height') ) ? get_option('rbi_big_pallet_max_height') : 0;
        $this->big_pallet_max_length = ( null !== get_option('rbi_big_pallet_max_length') ) ? get_option('rbi_big_pallet_max_length') : 0;
        //END Size Settings

        //START Free Shipping
          $this->free_cat_id = ( null !== get_option('rbi_free_cat_id') ) ? get_option('rbi_free_cat_id') : 0;
          $this->free_min_sum = ( null !== get_option('rbi_free_min_sum') ) ? get_option('rbi_free_min_sum') : 0;
        //END Free Shipping

        //Create Shipping Variants array
        $this->shipping_variant = array(
          'courier' => array(
            'price' =>   $this->courier_price,
            'max_weight' =>   $this->courier_max_weight,
            'max_width' =>   $this->courier_max_width,
            'max_height' =>   $this->courier_max_height,
            'max_length' =>   $this->courier_max_length,
          ),
          'small_pallet' => array(
            'price' =>   $this->small_pallet_price,
            'max_weight' =>   $this->small_pallet_max_weight,
            'max_width' =>   $this->small_pallet_max_width,
            'max_height' =>   $this->small_pallet_max_height,
            'max_length' =>   $this->small_pallet_max_length,
          ),
          'big_pallet' => array(
            'price' =>   $this->big_pallet_price,
            'max_weight' =>   $this->big_pallet_max_weight,
            'max_width' =>   $this->big_pallet_max_width,
            'max_height' =>   $this->big_pallet_max_height,
            'max_length' =>   $this->big_pallet_max_length,
          )
        );
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init() {
        // Load the settings API
        $this->init_form_fields();
        $this->init_settings();

        // Save settings in admin if you have any defined
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Define settings field for this shipping
     * @return void
     */
    function init_form_fields() {
      $this->form_fields = array(
        'enabled' => array(
          'title' => __( 'Enable', 'rbi_shipping' ),
          'type' => 'checkbox',
          'description' => __( 'Enable this shipping type.', 'rbi_shipping' ),
          'default' => 'yes'
        ),

        'title' => array(
          'title' => __( 'Title', 'rbi_shipping' ),
          'type' => 'text',
          'description' => __( 'Title to be display on site', 'rbi_shipping' ),
          'default' => __( 'RBI Calc Shipping', 'rbi_shipping' )
        ),

        /*'free_delivery_min_sum' => array(
          'title' => __( 'Free delivery minimal Sum', 'rbi_shipping' ),
          'type' => 'number',
          'description' => __( 'Minimal sum of 1 product from Free Shipping category', 'rbi_shipping' ),
          'default' => 2000
        ),*/

        /*'free_delivery_category' => array(
          'title' => __( 'Free delivery category ID', 'rbi_shipping' ),
          'type' => 'number',
          'description' => __( 'Free delivery category ID', 'rbi_shipping' ),
          'default' => 0
        )*/
      );

    }

    /**
     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
     *
     * @access public
     * @param mixed $package
     * @return void
     */

    public function calculate_shipping($package = array()) {

      $order_shipping_content = array(
        'big_pallet' => 0,
        'small_pallet' => 0,
        'courier' => 0
      );

      // product matrix defination
      $items_matrix = array(
        'courier' => array(),
        'small_pallet' => array(),
        'big_pallet' => array(),
      );

      //Prepare arrays for pruducts by category
      $courier_only_packet_products = array();
      $courier_packet_products = array();
      $small_pallet_products = array();
      $big_pallet_products = array();
      $free_shipping_products = array();
      $separate_shipping_cost_products = array();

      //separate products by category of shipping

      //Separate shiping products
      $separate_sipping_cost_products_array = array();
      $separate_sipping_cost_array = array();
      $separate_sipping_cost_sorted_array = array();
      $rbit_separate_shipping_rate_list = array();
      if (get_option("rbit_separate_shipping_rate_list")) {
        $rbit_separate_shipping_rate_list = get_option('rbit_separate_shipping_rate_list');
      }

      foreach ($rbit_separate_shipping_rate_list as $separate_sipping_cost_cat) {
        $separate_sipping_cost_array[] = $separate_sipping_cost_cat['id'];
        $separate_sipping_cost_sorted_array[$separate_sipping_cost_cat['id']] = $separate_sipping_cost_cat['price'];
      }


      //Check Free shipping
      $free_cat_id = $this->free_cat_id;

      $free_sum = 0;
      $have_free_shipping = false;
      $have_standart_shipping_products = false;
      foreach ( $package['contents'] as $values ) {
        $one_product = $values['data'];
        $product_all_categories = $one_product->get_category_ids();


        $free_min_sum = $this->free_min_sum;
        foreach ($product_all_categories as $value) {
          if ($value == $free_cat_id) $free_sum += $one_product->get_price() * $values['quantity'];
        }
      }
      if ($free_sum > $free_min_sum) $have_free_shipping = true;

      foreach ( $package['contents'] as  $values ) {
        $free_shipping_product = false;
        $one_product = $values['data'];
  
   
        //$one_product_categories =  $one_product->get_category_ids();
        $product_max_size = 10*$this->product_max_size($one_product);
  
  
        //Check free shipping category
        if ( $one_product->parent_id > 0 ) { //$one_product['parent_id']
          $parent_product = wc_get_product( $one_product->parent_id );
          $product_all_categories = $parent_product->get_category_ids();
        }
        else {
          $product_all_categories = $one_product->get_category_ids();
        }

        foreach ($product_all_categories as $category) {
          if ($category == $free_cat_id) $free_shipping_product = true;
        }

        
        $separate_sipping_array_by_categorie = array();
        $separate_sipping_array_by_categorie = array_intersect($product_all_categories, $separate_sipping_cost_array);

                
        if ($free_shipping_product && $have_free_shipping) {
          $free_shipping_products = $values;
        }
        elseif(count($separate_sipping_array_by_categorie) > 0) {
          //$test_mess = print_r($product_all_categories, true);
          $product_cat_with_price_array = array();
        
          foreach ($separate_sipping_array_by_categorie as $cat_id) {
            $product_cat_with_price_array[$cat_id] = $separate_sipping_cost_sorted_array[$cat_id];
          }

          $max_price_cat_id = array_keys($product_cat_with_price_array, max($product_cat_with_price_array))[0];
          $max_price_for_current_product_shipping = $product_cat_with_price_array[$max_price_cat_id];

          $values['shp_price'] = floatval($max_price_for_current_product_shipping);
          $values['shp_cat_id'] = $max_price_cat_id;
          $separate_shipping_cost_products[] = $values;

        }
        elseif ($this->can_put_in_courier_package($one_product)) {
          //can put it to courier package
          
          if ($this->can_put_in_small_pallet($one_product) || $this->can_put_in_big_pallet($one_product)) {
            $courier_packet_products[] = $values;
          }
          else {
            $courier_only_packet_products[] = $values;
          }

        }
        elseif($this->can_put_in_small_pallet($one_product)) {
          //can put it to small pallet
          $small_pallet_products[] = $values;
        }
        else {
          // all other products we put in courier box
          $big_pallet_products[] = $values;
        }


      }
      $add_mess .= ' count_products_cp='.count($courier_packet_products);
      $add_mess .= ' count_products_sp='.count($small_pallet_products);
      //$debug_mess .= '<br />'.'big pr-'.count($big_pallet_products);
      //$debug_mess .= '<br />'.'small pr-'.count($small_pallet_products);
      //$debug_mess .= '<br />'.'courier pr-'.count($courier_packet_products);

      //$debug_mess .= '/n'.'create items array';
      //create item array
      $big_pallet_items = $this->products_weight_and_volume_rate($this->create_items_array($big_pallet_products));
      $small_pallet_items = $this->products_weight_and_volume_rate($this->create_items_array($small_pallet_products));
      $courier_packet_items = $this->products_weight_and_volume_rate($this->create_items_array($courier_packet_products));
      $separate_shipping_cost_items = $this->create_items_array($separate_shipping_cost_products);
      $courier_only_packet_items = $this->create_items_array($courier_only_packet_products);

      $add_mess .= ' count_items_cp='.count($courier_packet_items);
      $add_mess .= ' count_items_sp='.count($small_pallet_items);

      $already_placed_courier_items = array();

      //$debug_mess .= '/n'.'items array created';

      $total_items_left = count($big_pallet_items) + count($small_pallet_items) + count($courier_packet_items);

      if ($total_items_left > 0) $have_standart_shipping_products = true;

      $need_big_pallet = 0;
      $need_small_pallet = 0;
      $need_courier_pack = 0;

      $left_weight_in_big_pallet_start = floatval($this->shipping_variant['big_pallet']['max_weight']);

      $left_volume_in_big_pallet_start = ($this->shipping_variant['big_pallet']['max_width']/1000) * ($this->shipping_variant['big_pallet']['max_height']/1000) * ($this->shipping_variant['big_pallet']['max_length']/1000);

      $left_weight_in_small_pallet_start = floatval($this->shipping_variant['small_pallet']['max_weight']);


      $left_volume_in_small_pallet_start = ($this->shipping_variant['small_pallet']['max_width']/1000) * ($this->shipping_variant['small_pallet']['max_height']/1000) * ($this->shipping_variant['small_pallet']['max_length']/1000);


      $left_weight_in_courier_pack = floatval($this->shipping_variant['courier']['max_weight']);
      $left_weight_in_courier_pack_start = $left_weight_in_courier_pack;


      $left_volume_in_courier_pack = ($this->shipping_variant['courier']['max_width']/1000) * ($this->shipping_variant['courier']['max_height']/1000) * ($this->shipping_variant['courier']['max_length']/1000);
      $left_volume_in_courier_pack_start = $left_volume_in_courier_pack;


      $left_weight_in_shp = $left_weight_in_courier_pack;
      $left_volume_in_shp = $left_volume_in_courier_pack;

      $left_weight_in_big_pallet = 0;
      $left_volume_in_big_pallet = 0;

      $left_weight_in_small_pallet = 0;
      $left_volume_in_small_pallet = 0;

      $left_weight_in_small_pallet_switch = 0;
      $left_volume_in_small_pallet_switch = 0;

      //================================================================
      //============= START shipping cost calculation ==================
      //================================================================

      while ($total_items_left > 0) {

        if (($left_weight_in_big_pallet > 0) &&($left_volume_in_big_pallet > 0)) {
          $free_space_in_big_pallet = true;
        } else {
          $free_space_in_big_pallet = false;
        };

        if (($left_weight_in_small_pallet > 0) &&($left_volume_in_small_pallet > 0)) {
          $free_space_in_small_pallet = true;
        } else {
          $free_space_in_small_pallet = false;
        };

        if (($left_weight_in_courier_pack > 0) &&($left_volume_in_courier_pack > 0)) {
          $free_space_in_courier_pack = true;
        } else {
          $free_space_in_courier_pack = false;
        };


        if (count($big_pallet_items)>0 ) {

          if ($need_big_pallet = 0) {
            $left_weight_in_big_pallet = $left_weight_in_big_pallet_start;
            $left_volume_in_big_pallet = $left_volume_in_big_pallet_start;
          }

          $big_pallet_items_sort_more_volume = $this->sort_products_put_more_volume($big_pallet_items);
          $put_in_big_pallet_response = $this->put_products_in_volume_and_weight($big_pallet_items_sort_more_volume, $left_weight_in_big_pallet, $left_volume_in_big_pallet, 'big_pallet');

          $big_pallet_items = $put_in_big_pallet_response['not_in_pack_items_array'];
          $left_weight_in_big_pallet = $put_in_big_pallet_response['weight_left'];
          $left_volume_in_big_pallet = $put_in_big_pallet_response['volume_left'];

          if (count($put_in_big_pallet_response['in_pack_items_array']) > 0) {
            $need_big_pallet++;
            //put in matrix items which are on big pallet

            $items_matrix['big_pallet'] = $this->add_items_to_array($items_matrix['big_pallet'], $put_in_big_pallet_response['in_pack_items_array']);
          }
          //if we put some products at the big pallet increase it

        }
        else {
          //if we dont use big pallet - we dont have space on it
          //$left_weight_in_big_pallet = 0;
          //$left_volume_in_big_pallet = 0;
        }

        if (count($small_pallet_items) > 0) {
          //if we have small pallet items then 1st step put it on big pallet free space
          
          $small_pallet_items_sort_more_volume = $this->sort_products_put_more_volume($small_pallet_items);
          $put_in_big_pallet_response = $this->put_products_in_volume_and_weight($small_pallet_items_sort_more_volume, $left_weight_in_big_pallet, $left_volume_in_big_pallet, 'big_pallet');

          $small_pallet_items = $put_in_big_pallet_response['not_in_pack_items_array'];
          $left_weight_in_big_pallet = $put_in_big_pallet_response['weight_left'];
          $left_volume_in_big_pallet = $put_in_big_pallet_response['volume_left'];
          
          if (count($put_in_big_pallet_response['in_pack_items_array']) > 0) {
            //put in matrix items which are on big pallet
            //$items_matrix['big_pallet'][$need_big_pallet] = $put_in_big_pallet_response['in_pack_items_array'];
            $items_matrix['big_pallet'] = $this->add_items_to_array($items_matrix['big_pallet'], $put_in_big_pallet_response['in_pack_items_array']);
          }

          if (count($small_pallet_items) > 0) {

            //$left_weight_in_small_pallet = $left_weight_in_small_pallet + $left_weight_in_small_pallet_start;
            //$left_volume_in_small_pallet = $left_volume_in_small_pallet + $left_volume_in_small_pallet_start;

            if ($need_small_pallet = 0) {
              $left_weight_in_small_pallet =  $left_weight_in_small_pallet_start;
              $left_volume_in_small_pallet =  $left_volume_in_small_pallet_start;
            }

            //if small pallet products left -  then 2nd step put it on small pallet free space
            $small_pallet_items_sort_more_weight = $this->sort_products_put_more_weight($small_pallet_items);
            $put_in_small_pallet_response = $this->put_products_in_volume_and_weight($small_pallet_items_sort_more_weight, $left_weight_in_small_pallet, $left_volume_in_small_pallet, 'small_pallet');

            $small_pallet_items = $put_in_small_pallet_response['not_in_pack_items_array'];
            $left_weight_in_small_pallet = $put_in_small_pallet_response['weight_left'];
            $left_volume_in_small_pallet = $put_in_small_pallet_response['volume_left'];

            if (count($put_in_small_pallet_response['in_pack_items_array']) > 0) {
              
              $need_small_pallet++;
              //put in matrix items which are on small pallet
              //$items_matrix['small_pallet'][$need_small_pallet] = $put_in_small_pallet_response['in_pack_items_array'];
              $items_matrix['small_pallet'] = $this->add_items_to_array($items_matrix['small_pallet'], $put_in_small_pallet_response['in_pack_items_array']);

            }
            //if we put some products to small pallet - increase it
          }

        }
        else {
          //if we dont use small pallet - we dont have space on it
          //$left_weight_in_small_pallet = 0;
          //$left_volume_in_small_pallet = 0;
        }
        $add_mess .= ' need_small_pallet='.$need_small_pallet;
        $add_mess .= ' count_sp_products='.count($small_pallet_items);


        if (count($courier_packet_items) > 0) {
          //if we have courier items then 1st step put it on big pallet free space
          $courier_packet_items_sort_more_volume = $this->sort_products_put_more_volume($courier_packet_items);
          $put_in_big_pallet_response = $this->put_products_in_volume_and_weight($courier_packet_items_sort_more_volume, $left_weight_in_big_pallet, $left_volume_in_big_pallet, 'big_pallet');

          $courier_packet_items = $put_in_big_pallet_response['not_in_pack_items_array'];
          $left_weight_in_big_pallet = $put_in_big_pallet_response['weight_left'];
          $left_volume_in_big_pallet = $put_in_big_pallet_response['volume_left'];

          if (count($put_in_big_pallet_response['in_pack_items_array']) > 0) {
            //put in matrix items which are on big pallet
            //$items_matrix['big_pallet'][$need_big_pallet] = $put_in_big_pallet_response['in_pack_items_array'];
            $items_matrix['big_pallet'] = $this->add_items_to_array($items_matrix['big_pallet'], $put_in_big_pallet_response['in_pack_items_array']);
          }


          if (count($courier_packet_items) > 0) {

            $left_weight_in_small_pallet_switch = 0;

            $left_volume_in_small_pallet_switch = 0;

            $add_mess3 .= ' |count_c_products_before_sp='.count($courier_packet_items);

            // if courier items left then 2nd step put courier items to small pallet free space
            $courier_packet_items_sort_more_weight = $this->sort_products_put_more_weight($courier_packet_items);
            $put_in_small_pallet_response = $this->put_products_in_volume_and_weight($courier_packet_items_sort_more_weight, $left_weight_in_small_pallet, $left_volume_in_small_pallet, 'small_pallet');


            $courier_packet_items = $put_in_small_pallet_response['not_in_pack_items_array'];
            $left_weight_in_small_pallet = $put_in_small_pallet_response['weight_left'];
            $left_volume_in_small_pallet = $put_in_small_pallet_response['volume_left'];

            $add_mess3 .= ' |count_c_products_after_sp='.count($courier_packet_items);

            if (count($put_in_small_pallet_response['in_pack_items_array']) > 0) {
          
              //put in matrix items which are on small pallet
              //$items_matrix['small_pallet'][$need_small_pallet] = $put_in_small_pallet_response['in_pack_items_array'];
              $items_matrix['small_pallet'] = $this->add_items_to_array($items_matrix['small_pallet'], $put_in_small_pallet_response['in_pack_items_array']);

            }


            if (count($courier_packet_items) > 0) {

              $left_weight_in_courier_pack = $left_weight_in_courier_pack_start;
              $left_volume_in_courier_pack = $left_volume_in_courier_pack_start;

              // if courier items left then 3rd step put courier items to courier packet
              $courier_packet_items_sort_more_weight = $this->sort_products_put_more_weight($courier_packet_items);
              $put_in_courier_packet_response = $this->put_products_in_volume_and_weight($courier_packet_items_sort_more_weight, $left_weight_in_courier_pack, $left_volume_in_courier_pack, 'courier');

              //remember start items array
              //if ($need_courier_pack == 0) $courier_packet_items_start = $courier_packet_items;

              $courier_packet_items = $put_in_courier_packet_response['not_in_pack_items_array'];

              $left_weight_in_courier_pack = $put_in_courier_packet_response['weight_left'];
              $left_volume_in_courier_pack = $put_in_courier_packet_response['volume_left'];

              $add_mess .= ' count_c_products_after_cp='.count($courier_packet_items);

              foreach ($put_in_courier_packet_response['in_pack_items_array'] as $item) {

                $already_placed_courier_items[] = $item;

              }

              if (count($put_in_courier_packet_response['in_pack_items_array']) > 0) {
                $need_courier_pack++;
                $items_matrix['courier_pack'] = $this->add_items_to_array($items_matrix['courier_pack'], $put_in_courier_packet_response['in_pack_items_array']);

                

              }

            }

            //$left_volume_in_small_pallet = $left_volume_in_small_pallet + $left_volume_in_small_pallet_switch;
            //$left_weight_in_small_pallet = $left_weight_in_small_pallet + $left_weight_in_small_pallet_switch;

          }

        }
        else {
          // code...
        }


        

        // Check shipping cost for current packages combination
        $courier_price = $need_courier_pack * $this->shipping_variant['courier']['price'];
        $big_pallet_price = $need_big_pallet * $this->shipping_variant['big_pallet']['price'];
        
        $add_mess3 .= ' |need_small_pallet='.$need_small_pallet;
        $add_mess3 .= ' |need_big_pallet='.$need_big_pallet;
        $add_mess3 .= ' |need_courier_pack='.$need_courier_pack;

        if($courier_price > $this->shipping_variant['small_pallet']['price']) {
          $repac_response_courier_items = array();
          $repack_need_courier_pack = 0;
          $repack_need_small_pallet = 0;
          
          $repack_courier_packet_items = $items_matrix['courier_pack'];

          $repack_put_in_small_pallet_response = $this->put_products_in_volume_and_weight($repack_courier_packet_items, $left_weight_in_small_pallet_start, $left_volume_in_small_pallet_start, 'small_pallet');
          $repack_courier_packet_items = $repack_put_in_small_pallet_response['not_in_pack_items_array'];
          if (count($repack_put_in_small_pallet_response['in_pack_items_array']) > 0) $repack_need_small_pallet++;

          if (count($repack_courier_packet_items) > 0) {
            while(count($repack_courier_packet_items) > 0) {
              $repack_put_in_courier_response = $this->put_products_in_volume_and_weight($repack_courier_packet_items, $left_weight_in_courier_pack_start, $left_volume_in_courier_pack_start, 'courier');

              $repack_courier_packet_items = $repack_put_in_courier_response['not_in_pack_items_array'];
              if (count($repack_put_in_courier_response['in_pack_items_array']) > 0) {
                $repack_need_courier_pack++;
              }
              $repac_response_courier_items = $this->add_items_to_array($repac_response_courier_items, $repack_put_in_courier_response['in_pack_items_array']);
            }
          }

          if (($repack_need_courier_pack * $this->shipping_variant['courier']['price'] + $repack_need_small_pallet * $this->shipping_variant['small_pallet']['price'] ) < $courier_price) {
            $items_matrix['small_pallet'] = $this->add_items_to_array($items_matrix['small_pallet'], $repack_put_in_small_pallet_response['in_pack_items_array']);
            $need_small_pallet++;

            $left_weight_in_small_pallet = $repack_put_in_small_pallet_response['weight_left'];
            $left_volume_in_small_pallet = $repack_put_in_small_pallet_response['volume_left'];



            $items_matrix['courier_pack'] = $this->add_items_to_array($items_matrix['courier_pack'], $repack_put_in_courier_response['in_pack_items_array']);
            $need_courier_pack = $repack_need_courier_pack;

            if($repack_need_courier_pack > 0) {
              $left_weight_in_courier_pack = $repack_put_in_courier_response['weight_left'];
              $left_volume_in_courier_pack = $repack_put_in_courier_response['volume_left'];
            }
            

          }

        
          /*
          if(count($put_in_small_pallet_response['not_in_pack_items_array']) == 0) {
            $need_small_pallet++;
            $left_volume_in_small_pallet = $left_volume_in_small_pallet + $left_volume_in_small_pallet_start;
            $left_weight_in_small_pallet = $left_weight_in_small_pallet + $left_weight_in_small_pallet_start;
            $courier_packet_items = $this->add_items_to_array($courier_packet_items, $items_matrix['courier_pack']);
            $items_matrix['courier_pack'] = array();
            $need_courier_pack = 0;
          }
          */

        }

        $small_pallet_price = $need_small_pallet * $this->shipping_variant['small_pallet']['price'];
        $courier_price = $need_courier_pack * $this->shipping_variant['courier']['price'];
        
        if ((($courier_price + $small_pallet_price)) > $this->shipping_variant['big_pallet']['price']) {
          $need_big_pallet++; //
          $left_weight_in_big_pallet = $left_weight_in_big_pallet + $left_weight_in_big_pallet_start;
          $left_volume_in_big_pallet = $left_volume_in_big_pallet + $left_volume_in_big_pallet_start;
          $courier_packet_items = $this->add_items_to_array($courier_packet_items, $items_matrix['courier_pack']);
          $items_matrix['courier_pack'] = array();
          $small_pallet_items = $this->add_items_to_array($small_pallet_items, $items_matrix['small_pallet']);
          $items_matrix['small_pallet'] = array();
          $need_courier_pack = 0;
          $need_small_pallet = 0;
        }

        
        $add_mess3 .= ' |need_small_pallet_after='.$need_small_pallet;
        $add_mess3 .= ' |need_big_pallet_after='.$need_big_pallet;
        $add_mess3 .= ' |need_courier_pack_after='.$need_courier_pack;

        $total_items_left = count($big_pallet_items) + count($small_pallet_items) + count($courier_packet_items);
      }

      //================================================================
      //=============== END shipping cost calculation ==================
      //================================================================

      //========================================================
      //====== START count courier packages for item which =====
      //========= can be shipped only by courier package =======
      //========================================================

      while (count($courier_only_packet_items) > 0) {
        //$courier_packet_items_sort_more_weight = $this->sort_products_put_more_weight($courier_packet_items);
        $put_in_courier_only_packet_response = $this->put_products_in_volume_and_weight($courier_only_packet_items, $left_weight_in_courier_pack, $left_volume_in_courier_pack, 'courier');

        $courier_only_packet_items = $put_in_courier_only_packet_response['not_in_pack_items_array'];

        if (count($courier_only_packet_items) > 0) {
          $need_courier_only_pack++;
          $left_weight_in_courier_pack = $left_weight_in_courier_pack_start;
          $left_volume_in_courier_pack = $left_volume_in_courier_pack_start;
        }

      }

      //========================================================
      //====== END count courier packages for item which =======
      //========= can be shipped only by courier package =======
      //========================================================



      //========================================================
      //=====START Separate Shipping Price products (shp)=======
      //========================================================
      $need_shp_courier_pack_array = array();
      $separate_shipping_total_price = 0;

      $separate_shipping_cost_items = $this->separate_shipping_cost_array_sort_by_price($separate_shipping_cost_items);

      //Items with separate shipping cost
      while (count($separate_shipping_cost_items) > 0) {

        //Try put it to big pallet
        if ($need_big_pallet > 0){
          $separate_shipping_cost_items = $this->separate_shipping_cost_array_sort_by_price($separate_shipping_cost_items);
          $put_in_big_pallet_shp_response = $this->put_products_in_volume_and_weight($separate_shipping_cost_items, $left_weight_in_big_pallet, $left_volume_in_big_pallet, 'big_pallet');
  
          $left_weight_in_big_pallet = $put_in_big_pallet_shp_response['weight_left'];
          $left_volume_in_big_pallet = $put_in_big_pallet_shp_response['volume_left'];
          $separate_shipping_cost_items = $put_in_big_pallet_shp_response['not_in_pack_items_array'];
        }

        //then try put it to small pallet
        if ($need_small_pallet > 0) {
          $separate_shipping_cost_items = $this->separate_shipping_cost_array_sort_by_price($separate_shipping_cost_items);
          $put_in_small_pallet_shp_response = $this->put_products_in_volume_and_weight($separate_shipping_cost_items, $left_weight_in_small_pallet, $left_volume_in_small_pallet, 'small_pallet');
  
          $left_weight_in_small_pallet = $put_in_small_pallet_shp_response['weight_left'];
          $left_volume_in_small_pallet = $put_in_small_pallet_shp_response['volume_left'];
          $separate_shipping_cost_items = $put_in_small_pallet_shp_response['not_in_pack_items_array'];
        }

        //$separate_shipping_cost_items = $this->separate_shipping_cost_array_sort_by_min_price($separate_shipping_cost_items);
        //then put it to courier box
        if (count($separate_shipping_cost_items) > 0) {
          //get_max_shipping_price()
          //$separate_shipping_cost_items = $this->sort_products_to_max_shipping_price($separate_shipping_cost_items);

          $separate_shipping_cost_items = $this->separate_shipping_cost_array_sort_by_price($separate_shipping_cost_items);

          $put_in_small_pallet_shp_response = $this->put_products_in_volume_and_weight($separate_shipping_cost_items, $left_weight_in_shp, $left_volume_in_shp, 'courier');
          
          $separate_shipping_cost_items = $put_in_small_pallet_shp_response['not_in_pack_items_array'];
          if (count($put_in_small_pallet_shp_response['in_pack_items_array']) > 0) {
            $max_price_in_this_box = $this->get_max_shipping_price($put_in_small_pallet_shp_response['in_pack_items_array']);
            $need_shp_courier_pack_array[] = $max_price_in_this_box;
            $separate_shipping_total_price = $separate_shipping_total_price + $max_price_in_this_box;
          }
          

        }


      }

      //================================================
      //=====END Separate Shipping Price products=======
      //================================================


      //total we need
      $order_shipping_content = array(
        'big_pallet' => $need_big_pallet,
        'small_pallet' => $need_small_pallet,
        'courier' => $need_courier_pack
      );

      $courier_only_shipping_price = $need_courier_only_pack * $this->shipping_variant['courier']['price'];

      $total_shipping_price = $need_big_pallet * $this->shipping_variant['big_pallet']['price'] + $need_small_pallet * $this->shipping_variant['small_pallet']['price'] + $need_courier_pack * $this->shipping_variant['courier']['price'] + $separate_shipping_total_price + $courier_only_shipping_price;
      //$total_shipping_price = $this->shipping_variant['small_pallet']['price'];
      /*$rate = array(
          'id' => $this->id,
          'label' => $this->title,
          'cost' => 10//$total_shipping_price
      );*/

      $rate = array(
          'id' => $this->id,
          'label' => $this->title,//.$add_mess, .$add_mess3
          'cost' => $total_shipping_price,//$total_shipping_price
          'taxes' => 'false',
      );

      $this->add_rate( $rate );


      // ++++++++++++++++++++++++++++++++++++++++++++++++++++
      // ++++++ Shipping cost display +++++++++++++++++++++++
      // ++++++++++++++++++++++++++++++++++++++++++++++++++++

      function rbi_shipping_description() {
        global $woocommerce;

        $free_cat_id = ( null !== get_option('rbi_free_cat_id') ) ? get_option('rbi_free_cat_id') : 0;

        $free_sum = 0;
        $standart_sum = 0;

        $items = $woocommerce->cart->get_cart();

        if (true) {
        ?>

        <?
        foreach($items as $item => $values) {
          $one_product = $values['data'];
          $free_shipping_product = false;
          $product_all_categories = $one_product->get_category_ids();
          foreach ($product_all_categories as $category) {
            if ($category == $free_cat_id) $free_shipping_product = true;
          }
          if ($free_shipping_product) {
          ?>
          <tr>
            <td class="subtotal-products" style="font-weight: 300;">
              <?echo $values['quantity'].' x '.$one_product->get_name();?>
            </td>
            <td class="subtotal-products" style="font-weight: 300;">
              <?echo wc_price( $values['quantity'] * $one_product->get_price());?>
            </td>
          </tr>
          <?
            $free_sum += $values['quantity'] * $one_product->get_price();
          }
        }
        ?>
        <tr class="woocommerce-shipping-totals shipping">
          <td>
          <? echo __( 'Free Shipping Products', 'rbi_shipping' );?>
          </td>
          <td>
            <? echo __( 'Subtotal', 'rbi_shipping' ).': '.wc_price($free_sum);?>
          </td>
        </tr>
        <tr class="woocommerce-shipping-totals shipping">
          <td>
          <? echo __( 'Free Shipping', 'rbi_shipping' );?>
          </td>
          <td>
            0,00 <?php echo get_woocommerce_currency_symbol(); ?>
          </td>
        </tr>
        <?
          if (true){
            $have_standart_shipping_products = false;
            foreach($items as $item => $values) {
              $free_shipping_product = false;
              $one_product = $values['data'];
              $product_all_categories = $one_product->get_category_ids();
              foreach ($product_all_categories as $category) {
                if ($category == $free_cat_id) $free_shipping_product = true;
                if ($category != $free_cat_id) $have_standart_shipping_products = true;
              }
              if ($free_shipping_product != true) {
              ?>
              <tr>
                <td class="subtotal-products" style="font-weight: 300;">
                  <?echo $values['quantity'].' x '.$one_product->get_name();?>
                </td>
                <td class="subtotal-products" style="font-weight: 300;">
                  <?echo wc_price( $values['quantity'] * $one_product->get_price());?>
                </td>
              </tr>
              <?
                $standart_sum += $values['quantity'] * $one_product->get_price();
              }
            }
            if ($have_standart_shipping_products) {
            ?>
            <tr>
              <td>
                <? echo __( 'Standart Shipping Products', 'rbi_shipping' );?>
              </td>
              <td>
                  <? echo __( 'Subtotal', 'rbi_shipping' ).': '.wc_price($standart_sum);?>
              </td>
            </tr>
            <?
            }
          }
        }
      }
      
      // If have Free Shipping products in order - display free shipping description
      if ($have_free_shipping) {
        add_action( 'woocommerce_cart_totals_before_shipping', 'rbi_shipping_description', 10, 2 );
      }


    }
///////////////////////////////////////////////////////
/////////////// Functions  ////////////////////////////
///////////////////////////////////////////////////////
  
    public function add_items_to_array($array, $new_items_array){
      foreach ($new_items_array as $item) {
        $array[] = $item;
      }
      return $array;
    }

  public function get_max_shipping_price($items) {

    $shp_price_array = array();
    foreach($items as $item) {
      $shp_price_array[] = floatval ($item['shp_price']);
    }
    return max($shp_price_array);
  }

  public function separate_shipping_cost_array_compare($a, $b) {
    return $b['shp_price'] <=> $a['shp_price'];

  }

  
  public function separate_shipping_cost_array_compare_min_price($a, $b) {
    return $a['shp_price'] <=> $b['shp_price'];
  }

  public function separate_shipping_cost_array_sort_by_price($cat_array) {
   usort($cat_array, [RBI_Shipping_Method::class, "separate_shipping_cost_array_compare"]);
   return $cat_array;
  }

  public function separate_shipping_cost_array_sort_by_min_price($cat_array) {
    usort($cat_array, [RBI_Shipping_Method::class, "separate_shipping_cost_array_compare_min_price"]);
    return $cat_array;
    }


  /*public function put_shp_products_in_volume_and_weight($shp_products, $left_weight_in_big_pallet, $left_volume_in_big_pallet){

    foreach($shp_products as $product_list){
      $items_array = $this->create_items_array($product_list)
    }

  }*/

    public function can_put_item_to_this_pack2($product, $pack_type_name) {
      $shipping_variant = $this->shipping_variant[$pack_type_name];
      if (floatval($product->get_weight()) <= $shipping_variant['max_weight']) {
        $product_sizes = array($product->get_length(), $product->get_width(), $product->get_height());
        $package_sizes = array($shipping_variant['max_length']/10, $shipping_variant['max_width']/10, $shipping_variant['max_height']/10);

        for($k=1; $k <= 3; $k++) {
          if (($package_sizes[0] >= $product_sizes[0]) && ($package_sizes[1] >= $product_sizes[1]) && ($package_sizes[2] >= $product_sizes[2])) return true;

          $product_sizes = $this->shift_array_in_left($product_sizes);
        }
        
      }

      return false;
    }

    public function can_put_item_to_this_pack($product, $pack_type_name) {
      $result = false;
      if ($pack_type_name == 'courier') $result = $this->can_put_in_courier_package($product);
      if ($pack_type_name == 'small_pallet') $result = $this->can_put_in_small_pallet($product);
      if ($pack_type_name == 'big_pallet') $result = $this->can_put_in_big_pallet($product);
      return $result;
    }

    public function shift_array_in_left ($arr) {
      $item = array_shift($arr);
      array_push ($arr,$item);
      return $arr;
     }

    public function can_put_in_courier_package($product) {
      
      if (floatval($product->get_weight()) <= $this->courier_max_weight) {
        $product_sizes = array($product->get_length(), $product->get_width(), $product->get_height());
        $package_sizes = array($this->courier_max_length/10, $this->courier_max_width/10, $this->courier_max_height/10);

        for($k=1; $k <= 3; $k++) {
          if (($package_sizes[0] >= $product_sizes[0]) && ($package_sizes[1] >= $product_sizes[1]) && ($package_sizes[2] >= $product_sizes[2])) return true;
          $product_sizes = $this->shift_array_in_left($product_sizes);
        }
        
      }

      return false;
      
    }

    public function can_put_in_small_pallet($product) {
      
      if (floatval($product->get_weight()) <= $this->small_pallet_max_weight) {
        $product_sizes = array($product->get_length(), $product->get_width(), $product->get_height());
        $package_sizes = array($this->small_pallet_max_length/10, $this->small_pallet_max_width/10, $this->small_pallet_max_height/10);

        for($k=1; $k <= 3; $k++) {
          if (($package_sizes[0] >= $product_sizes[0]) && ($package_sizes[1] >= $product_sizes[1]) && ($package_sizes[2] >= $product_sizes[2])) return true;
          $product_sizes = $this->shift_array_in_left($product_sizes);
        }
        
      }

      return false;
      
    }

    public function can_put_in_big_pallet($product) {
      
      if (floatval($product->get_weight()) <= $this->big_pallet_max_weight) {
        $product_sizes = array($product->get_length(), $product->get_width(), $product->get_height());
        $package_sizes = array($this->big_pallet_max_length/10, $this->big_pallet_max_width/10, $this->big_pallet_max_height/10);

        for($k=1; $k <= 3; $k++) {
          if (($package_sizes[0] >= $product_sizes[0]) && ($package_sizes[1] >= $product_sizes[1]) && ($package_sizes[2] >= $product_sizes[2])) return true;
          $product_sizes = $this->shift_array_in_left($product_sizes);
        }
        
      }

      return false;
      
    }


/*
    // count big pallets by weight of pruducts
    public function calc_pack_by_weight($items, $pack_max_weight) {
      $need_big_pallets = array();
      $total_weight = $this->calc_products_weight($big_pallet_products);

      $need_big_pallets['num'] = ceil($total_weight/$this->shipping_variant['big_pallet']['max_weight']);
      $need_big_pallets['total_weight'] = $total_weight;
      $need_big_pallets['weight_left'] = $need_big_pallets['num'] * $this->shipping_variant['big_pallet']['max_weight'] - $total_weight;

      return $need_big_pallets;
    }


    // count big pallets by volume of pruducts
    public function calc_pack_by_volume($big_pallet_products) {
      $need_big_pallets = array();
      $total_volume = $this->calc_products_volume($big_pallet_products);

      $big_pallet_max_volume = (($this->shipping_variant['big_pallet']['max_width']/1000) * ($this->shipping_variant['big_pallet']['max_height']/1000) * ($this->shipping_variant['big_pallet']['max_length']/1000));

      $need_big_pallets['num'] = ceil($total_weight/$big_pallet_max_volume);
      $need_big_pallets['total_volume'] = $total_volume;
      $need_big_pallets['volume_left'] = $need_big_pallets['num'] * $big_pallet_max_volume - $total_volume;

      return $need_big_pallets;
    }
*/
/*
    public function add_volume_to_product_array($products_list) {
      $result_array = array();
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];

        $one_value['rbi_item_volume'] = ($one_product->get_width()/100) * ($one_product->get_height()/100) * ($one_product->get_length()/100);
        $result_array[] = $one_value;
      }

      return $result_array;
    }
*/


    //создание массива с единицами товара
    public function create_items_array($products_list) {
      $items = array();
      $k = 0;
      foreach ($products_list as $one_value) {
        for ($i=0; $i < $one_value['quantity']; $i++) {
          $items[$k+$i] = $one_value;
        }
        $k = $k + $one_value['quantity'];
      }
      return $items;
    }

    // Try put items in weight and volume
    public function put_products_in_volume_and_weight($items, $free_weight, $free_volume, $pack_type_name = 'courier')
    {
      //courier, small_pallet, big_pallet
      
      $response_array = array();
      $in_pack_items_array = array();
      $not_in_pack_items_array = array();
      $space_left = 1;
      $weight_left = $free_weight;
      $volume_left = $free_volume;

      foreach ($items as $item) {
        $item_details = $item['data'];
        $item_volume = ($item_details->get_width()/100) * ($item_details->get_height()/100) * ($item_details->get_length()/100);
        $can_put_item_to_this_pack = $this->can_put_item_to_this_pack($item_details, $pack_type_name);
        if ((($weight_left - floatval($item_details->get_weight())) >= 0) && (($volume_left - $item_volume) >= 0) && $can_put_item_to_this_pack) {
          $in_pack_items_array[] = $item;
          $weight_left = $weight_left - $item_details->get_weight();
          $volume_left = $volume_left - $item_volume;
        }
        else {
          $not_in_pack_items_array[] = $item;
        }

      $response_array['in_pack_items_array'] = $in_pack_items_array;
      $response_array['not_in_pack_items_array'] = $not_in_pack_items_array;
      $response_array['weight_left'] = $weight_left;
      $response_array['volume_left'] = $volume_left;

      }

      return $response_array;
    }

    // calc products weight from given products list
    /*public function calc_products_weight($products_list) {
      $total_weight = 0;
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];
        $total_weight = $total_weight  + $one_product->get_weight() * $one_value['quantity'];
      }

      return $total_weight;
    }*/



    // calc products volume from given products list
    public function calc_products_volume($products_list) {
      $total_volume = 0;
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];
          $total_volume = $total_volume + $one_value['quantity'] * (($one_product->get_width()/100) * ($one_product->get_height()/100) * ($one_product->get_length()/100));
      }

      return $total_volume;
    }

    //calc kg|m3 rate for each product
    public function products_weight_and_volume_rate($products_list) {
      $new_products = array();
      foreach ($products_list as $one_value) {
        $one_product = $one_value['data'];
        $product_volume =  ($one_product->get_width()/100) * ($one_product->get_height()/100) * ($one_product->get_length()/100);
        //if product dont have parameters
        if ($product_volume == 0) $product_volume = 0.000001;
        $product_weight = floatval($one_product->get_weight());
        $one_value['weight_volume_rate'] = $product_weight/$product_volume;
        $new_products[] = $one_value;
      }

      //$new_products_sort = usort($new_products, );
      return $new_products;
    }

    public function sort_products_to_max_shipping_price($items) {
      usort($items, [RBI_Shipping_Method::class, "sort_products_to_max_shipping_price_callback"]);
      return $items;
    }

    public function sort_products_to_max_shipping_price_callback($product_a, $product_b) {
      if ($product_a['shp_price'] == $product_b['shp_price']) {
        return 0;
    }

    return ($product_a['shp_price'] < $product_b['shp_price']) ? -1 : 1;
    }
    
    public function sort_products_put_more_volume($products_list) {
      usort($products_list, [RBI_Shipping_Method::class, "sort_products_put_more_volume_callback"]);
      return $products_list;
    }

    public function sort_products_put_more_weight($products_list) {
      usort($products_list, [RBI_Shipping_Method::class, "sort_products_put_more_weight_callback"]);
      return $products_list;
    }



    public function sort_products_put_more_volume_callback($product_a, $product_b) {

      if ($product_a['weight_volume_rate'] == $product_b['weight_volume_rate']) {
          return 0;
      }
      return ($product_a['weight_volume_rate'] < $product_b['weight_volume_rate']) ? -1 : 1;
    }

    public function sort_products_put_more_weight_callback($product_a, $product_b) {

      if ($product_a['weight_volume_rate'] == $product_b['weight_volume_rate']) {
          return 0;
      }
      return ($product_a['weight_volume_rate'] > $product_b['weight_volume_rate']) ? -1 : 1;
    }


    //calculation of the number of pallets by weight and pallet type
    /*public function calc_pallets_by_weight($products_list, $pallet_type) {
      $need_pallets = array();
      $total_weight = $this->calc_products_weight($products_list);

      $need_pallets['float'] = $total_weight/$this->shipping_variant[$pallet_type]['max_weight'];
      $need_pallets['num'] = ceil($need_pallets['float']);
      $need_pallets['total_weight'] = $total_weight;
      $need_pallets['left'] = $need_pallets['num'] * $this->shipping_variant[$pallet_type]['max_weight'] - $total_weight;

      return $need_pallets;
    }*/

    //calculation of the number of pallets by volume and pallet type
    /*public function calc_pallets_by_volume($products_list, $pallet_type) {
      $need_big_pallets = array();
      $total_volume = $this->calc_products_volume($products_list);

      $pallet_max_volume = (($this->shipping_variant[$pallet_type]['max_width']/1000) * ($this->shipping_variant[$pallet_type]['max_height']/1000) * ($this->shipping_variant[$pallet_type]['max_length']/1000));

      $need_big_pallets['float'] = $total_volume/$pallet_max_volume;
      $need_big_pallets['num'] = ceil($need_big_pallets['float']);
      $need_big_pallets['total_volume'] = $total_volume;
      $need_big_pallets['left'] = $need_big_pallets['num'] * $pallet_max_volume - $total_volume;

      return $need_big_pallets;
    }*/

    // count big pallets by weight of pruducts
    /*public function calc_big_pallets_by_weight($big_pallet_products) {
      $need_big_pallets = array();
      $total_weight = $this->calc_products_weight($big_pallet_products);

      $need_big_pallets['num'] = ceil($total_weight/$this->shipping_variant['big_pallet']['max_weight']);
      $need_big_pallets['total_weight'] = $total_weight;
      $need_big_pallets['weight_left'] = $need_big_pallets['num'] * $this->shipping_variant['big_pallet']['max_weight'] - $total_weight;

      return $need_big_pallets;
    }*/


    // count big pallets by volume of pruducts
    /*public function calc_big_pallets_by_volume($big_pallet_products) {
      $need_big_pallets = array();
      $total_volume = $this->calc_products_volume($big_pallet_products);

      $big_pallet_max_volume = (($this->shipping_variant['big_pallet']['max_width']/1000) * ($this->shipping_variant['big_pallet']['max_height']/1000) * ($this->shipping_variant['big_pallet']['max_length']/1000));

      $need_big_pallets['num'] = ceil($total_weight/$big_pallet_max_volume);
      $need_big_pallets['total_volume'] = $total_volume;
      $need_big_pallets['volume_left'] = $need_big_pallets['num'] * $big_pallet_max_volume - $total_volume;

      return $need_big_pallets;
    }*/

    // check product max size with courier package max length, if the package fits - return TRUE
    public function courier_check_size($product_in_cart) {

      $courier_max_length = $this->courier_max_length;

      if (product_max_size($product_in_cart) <= $courier_max_length) return true;

      return false;

      //if ($courier_max_weight > $_product){}

    }

    // check product max size with small pallet max length, if the package fits - return TRUE
    public function small_pallet_check_size($product_in_cart) {

      $small_pallet_max_length = $this->small_pallet_max_length;

      if (product_max_size($product_in_cart) <= $small_pallet_max_length) return true;

      return false;

    }

    // check product max size with big pallet max length, if the package fits - return TRUE
    public function big_pallet_check_size($product_in_cart) {

      $big_pallet_max_length = $this->big_pallet_max_length;

      if ($this->product_max_size($product_in_cart) <= $big_pallet_max_length) return true;

      return false;

    }

    // returns the type of minimal packing required for this order
    public function check_size($all_products_in_cart) {
      $k = 1;
      $shipping_methods_priority = array( // min is better
        1 => 'courier',
        2 => 'small_pallet',
        3 => 'big_pallet'
      );

      $check_size_array = array();

      foreach ($all_products_in_cart as $one_product) {
        if (courier_check_size($one_product)) $check_size_array[$k] = 1;
        if (small_pallet_check_size($one_product)) $check_size_array[$k] = 2;
        if (big_pallet_check_size($one_product)) $check_size_array[$k] = 3;

        $k++;
      }
      return $shipping_methods_priority[max($check_size_array)];

    }

    // "rotate" the product and find it max size.
    public function product_max_size($one_product) {
      return max((float)$one_product->get_length(), (float)$one_product->get_width(), (float)$one_product->get_height());
    }

    public function pack_max_volume($pack_type) {
      return ($this->shipping_variant[$pack_type]['max_legth']/1000) * ($this->shipping_variant[$pack_type]['max_width']/1000) * ($this->shipping_variant[$pack_type]['max_height']/1000);
    }

}
