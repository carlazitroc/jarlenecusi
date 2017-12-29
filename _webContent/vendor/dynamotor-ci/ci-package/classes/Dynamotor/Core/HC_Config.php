<?php
namespace Dynamotor\Core;

use \CI_Config;

class HC_Config extends CI_Config
{


    public function __construct()
    {
        parent::__construct();

        // reset base url
        $base_url = $this->base_url();
        $this->set_item('base_url', $base_url);
    }

    public function get_script_path()
    {
        return substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
    }

    public function fix_http_url($path)
    {
        if (has_scheme($path)) {
            return $path;
        }

        if (substr($path, 0, 2) == '//') {
            if (is_https()) {
                $path = 'https:'.$path;
            } else {
                $path = 'http:'.$path;
            }
        }
        return $path;
    }

    public function base_url($uri = '', $protocol = null)
    {
        // if output does not a http-protocol format url, change it
        if (!preg_match('#^[a-z0-9]{1,}\:\/\/.+#', $uri)) {
            $prefix = '';

            $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'? 'https': 'http';

            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $port = ':'.( isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80);

            // Remove port number in the host name
            if (substr($host, - strlen($port), strlen($port)) == $port) {
                $host = substr($host, 0, strlen($host) - strlen($port));
            }
            if ($port == ':80') {
                $scheme = 'http';
                $port = '';
            };
            if ($port == ':443') {
                $scheme = 'https';
                $port = '';
            }

            if (is_https()) {
                $scheme = 'https';
            }

            if (substr($uri, 0, 1) != '/') {
                $prefix =$this->get_script_path();
            }

            $uri = $scheme.'://'.$host.$port.$prefix. $uri;
        }
        return $uri;
    }

    public function is_require_https()
    {
        return $this->item('require_https') == true;
    }

    public function site_url($uri = '', $protocol = null, $options = null)
    {
        if (is_array($uri)) {
            $uri = implode('/', $uri);
        }

        // Handle https request
        if ($this->is_require_https()) {
            if (empty($protocol)) {
                $protocol = 'https';
            }
        } else {
            if (empty($protocol)) {
                $protocol = is_https() ? 'https' :'http';
            }
        }
        
        if (function_exists('get_instance')) {
            if (empty($options) || (isset($options['localize']) && $options['localize'])) {
                $CI =& get_instance();
                $old_uri = $uri;
                $uri = $CI->lang->localize_url($uri);
                
                $tmp_locale_info = $CI->lang->parse_url($uri);
                //log_message('debug',get_class($this).'/site_url, '.$old_uri.' updated to '.$uri);
                
                
                if (isset($tmp_locale_info['locale'])) {
                    $locale_info = $CI->lang->get_locale($tmp_locale_info['locale']);
                    
                    $host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
                    if (substr($host, 0, 4) == 'www.') {
                        $host = substr($host, 4);
                    }
                    
                    if (isset($locale_info['host']) && !empty($host) && $locale_info['host'] != $host) {
                        $new_uri = $locale_info['host'].'/'.$uri;
                        $new_uri = (is_https() ? 'https' : 'http'). '://'.preg_replace('/\/+/', '/', $new_uri);
                        if (substr($new_uri, -1, 1) == '/') {
                            $new_uri = substr($new_uri, 0, strlen($new_uri)-1);
                        }
                        
                        //log_message('debug','MY_Config/site_url, uri="'.$uri.'", '.$new_uri);
                        return $new_uri;
                    }
                }
            }
        }

        return parent::site_url($uri, $protocol);
    }

    public function url($path = '', $type = 'base_url', $options = null)
    {
        //return $path;
        $path = stext($path);
        $path = fix_http_url($path);
        if (has_scheme($path)) {
            return $path;
        }

        
        $CI = &get_instance();
        
        $_url = $CI->config->item($type);
        if (empty($_url)) {
            $_url = base_url();
        }

        if (substr($_url, 0, 2) == '//') {
        } elseif (! has_scheme($_url)) {
            if (substr($_url, 0, 1) == '/') {
                $_url = ('//'.$_SERVER['HTTP_HOST'].$_url);
            }
        }
        $_url = fix_http_url($_url);


        if (substr($_url, -1, 1) !='/' && strlen($path)> 0 && substr($path, 0, 1)!='/') {
            $_url.='/';
        }

        return ($_url.$path);
    }

    public function web_url($uri = '', $options = null)
    {
        if (is_array($uri)) {
            $uri = implode('/', $uri);
        }
        
        if (function_exists('get_instance')) {
            if (empty($options) || (isset($options['localize']) && $options['localize'])) {
                $CI =& get_instance();
                $old_uri = $uri;
                $uri = $CI->lang->localize_url($uri);
                
                $tmp_locale_info = $CI->lang->parse_url($uri);
                
                //log_message('debug',get_class($this).'/web_url, '.$old_uri.' updated to '.$uri);
                if (isset($tmp_locale_info['locale'])) {
                    $locale_info = $CI->lang->get_locale($tmp_locale_info['locale']);
                    
                    $host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
                    if (substr($host, 0, 4) == 'www.') {
                        $host = substr($host, 4);
                    }
                    
                    if (isset($locale_info['host']) && !empty($host) && $locale_info['host'] != $host) {
                        $new_uri = $locale_info['host'].'/'.$uri;
                        $new_uri = 'http://'.preg_replace('/\/+/', '/', $new_uri);
                        if (substr($new_uri, -1, 1) == '/') {
                            $new_uri = substr($new_uri, 0, strlen($new_uri)-1);
                        }
                        
                        return $new_uri;
                    }
                }
            }
        }

        return $this->url($uri, 'web_url');
    }

    public function theme_url($path = '')
    {

        $theme = $this->item('theme');

        $url_prefix = $this->item('theme_url');
        if (empty($url_prefix)) {
            $url_prefix = $this->item('base_url');
            if (!has_tail_slash($url_prefix)) {
                $url_prefix .='/';
            }
            $url_prefix .= 'assets/themes/';
        }
        if (!has_tail_slash($url_prefix)) {
            $url_prefix .='/';
        }
        if (!empty($theme)) {
            $url_prefix .= $this->item('theme').'/';
        }
        
        return $this->url($url_prefix.$path, 'base_url');
    }
}

// END MY_Config Class

/* End of file MY_Config.php */
/* Location: ./system/application/libraries/MY_Config.php */
