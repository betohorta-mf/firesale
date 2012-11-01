<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* 
 * This class adds extra functionality to the stock
 * CodeIgniter Cart class without adding to the PyroCMS
 * core files.
 *
 * You can access the cart using:
 * $this->load->library('fs_cart');
 * $this->fs_cart->function();
 * 
 * Written by: Chris Harvey (FireSALE Team)
 */

// Include the stock CodeIgniter Cart class
require_once(BASEPATH.'libraries/Cart.php');

class Fs_cart extends CI_Cart
{
	public $product_name_safe   = FALSE;
	public $product_name_rules	= '\.\:\-_ a-z0-9_-а-яА-Я ';

	public function __construct()
	{
		parent::__construct();

		$this->ci =& get_instance();

		// Load the required models
		$this->ci->load->model('firesale/currency_m');
	}

	public function destroy()
	{
		// Run the standard CI_Cart function
		parent::destroy();

		// Fire an event to tell external modules that the cart has been destroyed
		Events::trigger('cart_destroyed');
	}

	public function currency()
	{
		if ( ! isset($this->currency))
		{
			$currency = $this->ci->session->userdata('currency');
			$this->currency = $this->ci->currency_m->get($currency ? $currency : 1);
		}

		return $this->currency;
	}

	public function tax_mod()
	{
		if ( ! isset($this->tax_mod))
		{
			$this->tax_mod = 1 - ($this->currency()->cur_tax / 100);
		}

		return $this->tax_mod;
	}

	public function tax_rate()
	{
		return $this->currency()->cur_tax;
	}

	public function tax()
	{
		if ( ! isset($this->tax))
		{
			$this->tax = $this->total() / (($this->tax_rate() / 100) + 1) * (1 - $this->tax_mod());
		}

		return $this->tax;
	}

	public function subtotal()
	{
		return $this->subtotal = ($this->total() - $this->tax());
	}
}