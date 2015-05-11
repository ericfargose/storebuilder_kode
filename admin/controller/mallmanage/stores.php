<?php
class ControllerMallmanageStores extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('mallmanage/stores');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('mallmanage/stores');

        $this->getList();
    }

    public function add() {
        $this->load->language('mallmanage/stores');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('mallmanage/stores');

        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            
            $sub_dir = DOMAIN;
            $part = explode('.',DOMAIN);
            if(sizeof($part) == 2){
                $domain = DOMAIN;
            }else{
                $domain = $part[sizeof($part)-2].'.'.$part[sizeof($part)-1];
            }
            $domain = DOMAIN;
            

            if(isset($this->request->post['subdir']) AND $this->request->post['subdir']){
                $this->request->post['subdir'] = 'http://'. $sub_dir .'/'.$this->request->post['subdir'].'/' ;
                $this->request->post['store_url'] = $this->request->post['subdir'];
                $this->request->post['url_type'] = 'subdir';
                $this->request->post['main_domain'] = $sub_dir;
            }

            if(isset($this->request->post['subdomain']) AND $this->request->post['subdomain']){
                $this->request->post['subdomain'] = 'http://' .$this->request->post['subdomain']. '.' . $domain .'/' ;
                $this->request->post['store_url'] = $this->request->post['subdomain'];
                $this->request->post['url_type'] = 'subdomain';
                $this->request->post['main_domain'] = $domain;
            }

            if(isset($this->request->post['tld']) AND $this->request->post['tld']){

                $this->request->post['tld'] = 'http://' .strtolower($this->request->post['tld']).'/';
                $this->request->post['store_url'] = $this->request->post['tld'] ;
                $this->request->post['url_type'] = 'tld';

                $url_data = parse_url(str_replace('&amp;', '&',$this->request->post['tld']));
                $part = explode('.',$url_data['host']);
                if(sizeof($part) == 2){
                    $this->request->post['main_domain'] = $url_data['host'];
                }else{
                    $this->request->post['main_domain'] = $part[sizeof($part)-2].'.'.$part[sizeof($part)-1];
                }
            }

            $store_id = $this->model_mallmanage_stores->newStore($this->request->post);
            $this->load->helper('curlhandler');
            if($store_id) {
                $store = 'sb_'.$store_id;
                $this->request->post['db_new_name'] = $store; 
                $this->request->post['store_id'] = $store_id;
                CurlHandler::Request('dbcreationapi', 'createstore', $this->request->post);
                CurlHandler::Request('associatefolders', 'createfolders', $this->request->post);
            }
            $this->session->data['success'] = sprintf($this->language->get('success_store'), $this->request->post['name'], $store_id);
            $this->response->redirect($this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->getStoreForm();
    }

    public function edit() {
        $this->load->language('mallmanage/stores');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('mallmanage/stores');

        //if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_mallmanage_stores->editStore($this->request->post, $this->request->get['store_id']);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_url'])) {
                $url .= '&filter_url=' . urlencode(html_entity_decode($this->request->get['filter_url'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('mallmanage/stores');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('mallmanage/stores');
        $this->load->helper('curlhandler');
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $store_id) {
                $this->model_mallmanage_stores->deleteStore($store_id);
                //CurlHandler::Request('storeapi', 'deleteStore', $data);
                $data = array('store_id' => $store_id);
                CurlHandler::Request('dbdeletionapi', 'deleteStore', $data);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_url'])) {
                $url .= '&filter_url=' . urlencode(html_entity_decode($this->request->get['filter_url'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = null;
        }

        if (isset($this->request->get['filter_url'])) {
            $filter_url = $this->request->get['filter_url'];
        } else {
            $filter_url = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 's.store_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_url'])) {
            $url .= '&filter_url=' . urlencode(html_entity_decode($this->request->get['filter_url'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );

        $data['add'] = $this->url->link('mallmanage/stores/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $data['delete'] = $this->url->link('mallmanage/stores/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

        $data['stores'] = array();

        $filter_data = array(
            'filter_name'     => $filter_name,
            'filter_status'   => $filter_status,
            'filter_url'      => $filter_url,
            'sort'            => $sort,
            'order'           => $order,
            'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'           => $this->config->get('config_limit_admin')
        );

        $store_total = $this->model_mallmanage_stores->getTotalStores($filter_data);

        $results = $this->model_mallmanage_stores->getStores($filter_data);
        
        foreach ($results as $result) {
            
            $data['stores'][] = array(
                'store_id'   => $result['store_id'],
                'name'       => $result['name'],
                'url'        => $result['url'],
                'status'     => $result['status'],
                'edit'       => $this->url->link('mallmanage/stores/edit', 'token=' . $this->session->data['token'] . '&store_id=' . $result['store_id'] . $url, 'SSL')
            );
        }

        $data['heading_title'] = $this->language->get('heading_title');
        
        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');

        $data['text_active'] = $this->language->get('text_active');
        $data['text_inactive'] = $this->language->get('text_inactive');

        $data['column_name'] = $this->language->get('column_name');
        $data['column_url'] = $this->language->get('column_url');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_action'] = $this->language->get('column_action');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_url'] = $this->language->get('entry_url');
        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');

        $data['token'] = $this->session->data['token'];

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_url'])) {
            $url .= '&filter_url=' . urlencode(html_entity_decode($this->request->get['filter_url'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . '&sort=s.name' . $url, 'SSL');
        $data['sort_url'] = $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . '&sort=s.url' . $url, 'SSL');
        $data['sort_status'] = $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . '&sort=xsm.status' . $url, 'SSL');

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_url'])) {
            $url .= '&filter_url=' . urlencode(html_entity_decode($this->request->get['filter_url'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $store_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($store_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($store_total - $this->config->get('config_limit_admin'))) ? $store_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $store_total, ceil($store_total / $this->config->get('config_limit_admin')));

        $data['filter_name'] = $filter_name;
        $data['filter_url'] = $filter_url;
        $data['filter_status'] = $filter_status;

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('mallmanage/store_list.tpl', $data));
    }

    protected function getForm() {
        $data['heading_title'] = $this->language->get('heading_title');
        
        $data['text_form'] = !isset($this->request->get['store_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        
        $data['text_subdirectory'] = $this->language->get('text_subdirectory');
        $data['text_subdomain'] = $this->language->get('text_subdomain');
        $data['text_top_level_domain'] = $this->language->get('text_top_level_domain');
        $data['text_external_domain'] = $this->language->get('text_external_domain');

        $data['text_active'] = $this->language->get('text_active');
        $data['text_inactive'] = $this->language->get('text_inactive');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_url'] = $this->language->get('entry_url');
        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_username'] = $this->language->get('entry_username');
        $data['entry_password'] = $this->language->get('entry_password');
        $data['entry_status'] = $this->language->get('entry_status');

        

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        $data['text_dns_message'] = sprintf($this->language->get('text_dns_message'), DNS_POINTING);

        $data['sub_dir'] = DOMAIN;
        $part = explode('.',DOMAIN);
        if(sizeof($part) == 2){
            $data['domain'] = DOMAIN;
        }else{
            $data['domain'] = $part[sizeof($part)-2].'.'.$part[sizeof($part)-1];
        }
    
        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        if (isset($this->error['storeurl'])) {
            $data['error_storeurl'] = $this->error['storeurl'];
        } else {
            $data['error_storeurl'] = array();
        }

        if (isset($this->error['storename'])) {
            $data['error_storename'] = $this->error['storename'];
        } else {
            $data['error_storename'] = array();
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        if (isset($this->error['store_url_type'])) {
            $data['error_store_url_type'] = $this->error['store_url_type'];
        } else {
            $data['error_store_url_type'] = array();
        }

        if (isset($this->error['store_url_unique'])) {
            $data['error_store_url_unique'] = $this->error['store_url_unique'];
        } else {
            $data['error_store_url_unique'] = array();
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_url'])) {
            $url .= '&filter_url=' . urlencode(html_entity_decode($this->request->get['filter_url'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('mallmanage/stores/edit', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );

        if (!isset($this->request->get['store_id'])) {
            $data['action'] = $this->url->link('mallmanage/stores/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $data['action'] = $this->url->link('mallmanage/stores/edit', 'token=' . $this->session->data['token'] . '&store_id=' . $this->request->get['store_id'] . $url, 'SSL');
        }

        $data['cancel'] = $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . $url, 'SSL');

        if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $store_info = $this->model_mallmanage_stores->getStore($this->request->get['store_id']);
        }

        $data['token'] = $this->session->data['token'];

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($store_info)) {
            $data['name'] = $store_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['store_url_type'])) {
            $data['store_url_type'] = $this->request->post['store_url_type'];
        } elseif (!empty($store_info)) {
            $data['store_url_type'] = $store_info['url_mode'];
        } else {
            $data['store_url_type'] = '';
        }

        $data['storeurl'] = '';
        if($data['store_url_type'] != ''){
            if($data['store_url_type'] == 'subdir'){
                if(isset($this->request->post['subdir'])){
                    $data['storeurl'] = $this->request->post['subdir'];
                } elseif (!empty($store_info)) {
                    $data['storeurl'] = $store_info['url'];
                } else {
                    $data['storeurl'] = '';
                }
            } elseif($data['store_url_type'] == 'subdomain'){
                if(isset($this->request->post['subdomain'])){
                    $data['storeurl'] = $this->request->post['subdomain'];
                } elseif (!empty($store_info)) {
                    $data['storeurl'] = $store_info['url'];
                } else {
                    $data['storeurl'] = '';
                }
            } elseif($data['store_url_type'] == 'tld'){
                if(isset($this->request->post['tld'])){
                    $data['storeurl'] = $this->request->post['tld'];
                } elseif (!empty($store_info)) {
                    $data['storeurl'] = $store_info['url'];
                } else {
                    $data['storeurl'] = '';
                }
            }
        }

        if (isset($this->request->post['store_status'])) {
            $data['store_status'] = $this->request->post['store_status'];
        } elseif (!empty($store_info)) {
            $data['store_status'] = $store_info['status'];
        } else {
            $data['store_status'] = 'active';
        }

        if(isset($this->request->post['tld'])){
            $data['tld'] = $this->request->post['tld']; 
        }else{
            $data['tld'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('mallmanage/store_form_edit.tpl', $data));
    }

    protected function getStoreForm() {
        $data['heading_title'] = $this->language->get('heading_title');
        
        $data['text_form'] = !isset($this->request->get['store_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        
        $data['text_subdirectory'] = $this->language->get('text_subdirectory');
        $data['text_subdomain'] = $this->language->get('text_subdomain');
        $data['text_top_level_domain'] = $this->language->get('text_top_level_domain');
        $data['text_external_domain'] = $this->language->get('text_external_domain');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_url'] = $this->language->get('entry_url');
        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_username'] = $this->language->get('entry_username');
        $data['entry_password'] = $this->language->get('entry_password');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        $data['text_dns_message'] = sprintf($this->language->get('text_dns_message'), DNS_POINTING);

        $data['sub_dir'] = DOMAIN;
        $part = explode('.',DOMAIN);
        if(sizeof($part) == 2){
            $data['domain'] = DOMAIN;
        }else{
            $data['domain'] = $part[sizeof($part)-4].'.'.$part[sizeof($part)-3].'.'.$part[sizeof($part)-2].'.'.$part[sizeof($part)-1];
        }
    
        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        if (isset($this->error['storeurl'])) {
            $data['error_storeurl'] = $this->error['storeurl'];
        } else {
            $data['error_storeurl'] = array();
        }

        if (isset($this->error['storename'])) {
            $data['error_storename'] = $this->error['storename'];
        } else {
            $data['error_storename'] = array();
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = array();
        }

        if (isset($this->error['username'])) {
            $data['error_username'] = $this->error['username'];
        } else {
            $data['error_username'] = array();
        }

        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = array();
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        if (isset($this->error['store_url_type'])) {
            $data['error_store_url_type'] = $this->error['store_url_type'];
        } else {
            $data['error_store_url_type'] = array();
        }

        if (isset($this->error['store_url_unique'])) {
            $data['error_store_url_unique'] = $this->error['store_url_unique'];
        } else {
            $data['error_store_url_unique'] = array();
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_url'])) {
            $url .= '&filter_url=' . urlencode(html_entity_decode($this->request->get['filter_url'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . $url, 'SSL')
        );

        if (!isset($this->request->get['store_id'])) {
            $data['action'] = $this->url->link('mallmanage/stores/add', 'token=' . $this->session->data['token'] . $url, 'SSL');
        } else {
            $data['action'] = $this->url->link('mallmanage/stores/edit', 'token=' . $this->session->data['token'] . '&store_id=' . $this->request->get['store_id'] . $url, 'SSL');
        }

        $data['cancel'] = $this->url->link('mallmanage/stores', 'token=' . $this->session->data['token'] . $url, 'SSL');

        if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $store_info = $this->model_mallmanage_stores->getStore($this->request->get['store_id']);
        }

        $data['token'] = $this->session->data['token'];

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($store_info)) {
            $data['name'] = $store_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['store_url_type'])) {
            $data['store_url_type'] = $this->request->post['store_url_type'];
        } elseif (!empty($store_info)) {
            $data['store_url_type'] = $store_info['store_url_type'];
        } else {
            $data['store_url_type'] = '';
        }

        if (isset($this->request->post['username'])) {
            $data['username'] = $this->request->post['username'];
        } elseif (!empty($store_info)) {
            $data['username'] = $store_info['username'];
        } else {
            $data['username'] = '';
        }

        if (isset($this->request->post['password'])) {
            $data['password'] = $this->request->post['password'];
        } elseif (!empty($store_info)) {
            $data['password'] = $store_info['password'];
        } else {
            $data['password'] = '';
        }

        if (isset($this->request->post['email'])) {
            $data['email'] = $this->request->post['email'];
        } elseif (!empty($store_info)) {
            $data['email'] = $store_info['email'];
        } else {
            $data['email'] = '';
        }

        $data['storeurl'] = '';
        if($data['store_url_type'] != ''){
            if($data['store_url_type'] == 'subdir'){
                if(isset($this->request->post['subdir'])){
                    $data['storeurl'] = $this->request->post['subdir'];
                } else {
                    $data['storeurl'] = '';
                }
            } elseif($data['store_url_type'] == 'subdomain'){
                if(isset($this->request->post['subdomain'])){
                    $data['storeurl'] = $this->request->post['subdomain'];
                } else {
                    $data['storeurl'] = '';
                }
            } elseif($data['store_url_type'] == 'tld'){
                if(isset($this->request->post['tld'])){
                    $data['storeurl'] = $this->request->post['tld'];
                } else {
                    $data['storeurl'] = '';
                }
            }
        }
        if(isset($this->request->post['tld'])){
            $data['tld'] = $this->request->post['tld']; 
        }else{
            $data['tld'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('mallmanage/store_form.tpl', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'mallmanage/stores')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        } else {

            if (isset($this->request->post['subdomain'])) {
                $store_url = $this->request->post['subdomain'];
            } elseif(isset($this->request->post['subdir'])) {
                $store_url = $this->request->post['subdir'];
            } elseif(isset($this->request->post['tld'])){
                $store_url = $this->request->post['tld'];
            }

            $domain = DOMAIN;
            
            if(isset($this->request->post['subdir'])){
                $url = 'http://'.$domain.'/'.$store_url.'/';
                
                $already_store = $this->model_mallmanage_stores->CheckExists('sb_store', 'url', $url);
                if($already_store){
                    $this->error['storeurl'] = $this->language->get('error_store_url_unique');
                }

                $already_store = $this->model_mallmanage_stores->CheckExists('sb_store', 'name', $this->request->post['name']);
                if($already_store){
                    $this->error['name'] = $this->language->get('error_store_name_unique');
                }

            } else {
                $already_store = $this->model_mallmanage_stores->CheckExists('sb_store', 'name', $this->request->post['name']);
                if($already_store){
                    $this->error['name'] = $this->language->get('error_store_name_unique');
                }   

                $url = 'http://'.$domain.'/'.$store_url.'/';
                $already_store = $this->model_mallmanage_stores->CheckExists('sb_store', 'url', $url);
                if($already_store){
                    $this->error['storeurl'] = $this->language->get('error_store_url_unique');
                }
            }
            
        }

        if (empty($this->request->post['store_url_type'])) {
            $this->error['store_url_unique'] = $this->language->get('error_store_url_type');
        } else{
            $pattern = '/[^a-zA-z0-9_\-]/';
            if (isset($this->request->post['subdomain'])) {
                $store_url = $this->request->post['subdomain'];
                $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-._]+[a-zA-Z0-9]$/';
            } elseif(isset($this->request->post['subdir'])) {
                $store_url = $this->request->post['subdir'];
                $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-._]+[a-zA-Z0-9]$/';
            } elseif(isset($this->request->post['tld'])){
                $store_url = $this->request->post['tld'];
                $store_url_explode = explode('.', $store_url);
                if(count($store_url_explode) == 2){
                    $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9]{2,20}$/';
                } elseif(count($store_url_explode) == 3){
                    $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9-._]+[^.]+[a-zA-Z0-9]$/';
                } else {
                    $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9-._]+[^.]+[a-zA-Z0-9]{1,20}$/';    
                }
            }

            if (!preg_match($re, $store_url)) {
                $this->error['storeurl'] = $this->language->get('error_storeurl');
            }
            
        }
        
        if ((utf8_strlen($this->request->post['email']) < 1) || (utf8_strlen($this->request->post['email']) > 64)) {
            $this->error['email'] = $this->language->get('error_email');
        }

        $pattern = '/^[A-Z0-9-._%-+]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,20}$/i';

        if (!preg_match($pattern, $this->request->post['email'])) {
            $this->error['email'] = $this->language->get('error_email');
        }

        if ((utf8_strlen($this->request->post['username']) < 1) || (utf8_strlen($this->request->post['username']) > 64)) {
            $this->error['username'] = $this->language->get('error_username');
        }

        if ((utf8_strlen($this->request->post['password']) < 1) || (utf8_strlen($this->request->post['password']) > 64)) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if ((utf8_strlen($this->request->post['store_url_type']) < 1) || (utf8_strlen($this->request->post['store_url_type']) > 64)) {
            $this->error['store_url_type'] = $this->language->get('error_url');
        }
        
        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function autocomplete() {
        $json = array();

        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_url'])) {
            $this->load->model('mallmanage/stores');
            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['filter_url'])) {
                $filter_url = $this->request->get['filter_url'];
            } else {
                $filter_url = '';
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 5;
            }

            $filter_data = array(
                'filter_name'  => $filter_name,
                'filter_url' => $filter_url,
                'start'        => 0,
                'limit'        => $limit
            );

            $results = $this->model_mallmanage_stores->getStores($filter_data);

            foreach ($results as $result) {
                $json[] = array(
                    'store_id' => $result['store_id'],
                    'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                );
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}