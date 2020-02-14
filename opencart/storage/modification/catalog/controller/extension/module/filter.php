<?php
class ControllerExtensionModuleFilter extends Controller {
	public function index() {
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		$category_id = end($parts);

		$this->load->model('catalog/category');

		$this->load->model('extension/module/technics');	// technics			
            

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$this->load->language('extension/module/filter');

// technics
			$this->load->language('extension/theme/technics');
			$data['text_technics_filter'] = $this->language->get('text_technics_filter');
			$data['text_technics_from'] = $this->language->get('text_technics_from');
			$data['text_technics_to'] = $this->language->get('text_technics_to');
			$data['text_technics_show'] = $this->language->get('text_technics_show');
			$data['text_technics_reset'] = $this->language->get('text_technics_reset');
			$data['text_technics_filter_price'] = $this->language->get('text_technics_filter_price');
			$dataFilter['category_id'] = $category_id;
			$data['priceLimits'] = $this->model_extension_module_technics->getPriceLimits($dataFilter);

			if (isset($this->request->get['min_price'])) {
				$data['min_price'] = (int)$this->request->get['min_price'];
			}else{
				$data['min_price'] = (int)$data['priceLimits']['min_price'];
			}

			if (isset($this->request->get['max_price'])) {
				$data['max_price'] = round($this->request->get['max_price'],2);
			}else{
				$data['max_price'] = round($data['priceLimits']['max_price'],2); 
			}

			$data['min_price_format'] = $this->currency->format($data['min_price'], $this->session->data['currency'],1);
			$data['max_price_format'] = $this->currency->format($data['max_price'], $this->session->data['currency'],1);
			$data['category_id'] = $category_id;
// technics end
            

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url));

			if (isset($this->request->get['filter'])) {
				$data['filter_category'] = explode(',', $this->request->get['filter']);
			} else {
				$data['filter_category'] = array();
			}

			$this->load->model('catalog/product');

			$data['filter_groups'] = array();

			$filter_groups = $this->model_catalog_category->getCategoryFilters($category_id);

			if ($filter_groups) {
				foreach ($filter_groups as $filter_group) {
					$childen_data = array();

					foreach ($filter_group['filter'] as $filter) {
						$filter_data = array(
							'filter_category_id' => $category_id,
							'filter_filter'      => $filter['filter_id']

							,'filter_sub_category' => $this->config->get('theme_technics_subcategory') // technics
            
						);


						// technics
						$isactive = true;
						$count = '';

						$count = $this->model_catalog_product->getTotalProducts($filter_data);
						if (!$count) {
							$isactive = false;
						}
						// technics end			
            
						$childen_data[] = array(
							'filter_id' => $filter['filter_id'],
							
							'name'      => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $count . ')' : ''), // technics
							'isactive'	=> $isactive // technics		
            
						);
					}

					$data['filter_groups'][] = array(
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'filter'          => $childen_data
					);
				}

				return $this->load->view('extension/module/filter', $data);
			}
		}
	}
}