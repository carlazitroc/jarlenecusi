<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * HMAC helper
 *
 * $signature = hex2b64(hmac_sha1($key, $data));
 *
 * @package        stensi
 * @category    Helpers
 * @author        stensi
 * @link        http://stensi.com
 */

// ------------------------------------------------------------------------

/**
 * HMAC
 *
 * Calculate HMAC according to RFC 2104, for chosen algorithm.
 * http://www.ietf.org/rfc/rfc2104.txt
 *
 * @access    public
 * @param    string    hash algorithm
 * @param    string    key to sign hash with
 * @param    string    data to be hashed
 * @return    string
 */
if (!function_exists('hmac'))
{
    function hmac($hashfunc, $key, $data)
    {
        $blocksize=64;

        if (strlen($key) > $blocksize)
        {
            $key = pack('H*', $hashfunc($key));
        }

        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $data))));

        return bin2hex($hmac);
    }
}

// ------------------------------------------------------------------------

/**
 * HMAC-SHA1
 *
 * Calculate HMAC-SHA1 according to RFC 2104.
 * http://www.ietf.org/rfc/rfc2104.txt
 *
 * @access    public
 * @param    string    key to sign hash with
 * @param    string    data to be hashed
 * @return    string
 */
if (!function_exists('hmac_sha1'))
{
    function hmac_sha1($key, $data)
    {
        return hmac('sha1', $key, $data);
    }
}

// ------------------------------------------------------------------------

/**
 * HMAC-MD5
 *
 * Calculate HMAC-MD5 according to RFC 2104.
 * http://www.ietf.org/rfc/rfc2104.txt
 *
 * @access    public
 * @param    string    key to sign hash with
 * @param    string    data to be hashed
 * @return    string
 */
if (!function_exists('hmac_md5'))
{
    function hmac_md5($key, $data)
    {
        return hmac('md5', $key, $data);
    }
}

// ------------------------------------------------------------------------

/**
 * Hex to Base64
 *
 * Convert hex to base64.
 *
 * @access    public
 * @param    string
 * @return    string
 */
if (!function_exists('hex2b64'))
{
    function hex2b64($str)
    {
        $raw = '';

        for ($i = 0; $i < strlen($str); $i += 2)
        {
            $raw .= chr(hexdec(substr($str, $i, 2)));
        }

        return base64_encode($raw);
    }
}

/* End of file hmac_helper.php */
/* Location: ./application/helpers/hmac_helper.php */ 