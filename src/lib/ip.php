<?php
/**
 * ipip.net
 * ip库
 */

namespace phpshow\lib;


class ip
{
    private $file;
    private $offset;
    private $index;
    /**
     * @param string $path is file path
     */
    public function __construct()
    {
        //增加
        $path = PS_PATH.'/lib/17monipdb.datx';
        $this->file = fopen($path, 'rb');
        $this->offset = unpack('Nlen', fread($this->file, 4));
        $this->index = fread($this->file, $this->offset['len'] - 4);
    }
    /**
     * @param string $ip
     *
     * @return bool|array
     */
    public function find($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === FALSE)
        {
            return FALSE; // or throw Exception?
        }
        $nip2 = pack('N', ip2long($ip));
        $ips = explode('.', $ip);
        $idx = (256 * $ips[0] + $ips[1]) * 4;
        $start = unpack('Vlen', substr($this->index, $idx, 4));
        $off = NULL;
        $len = NULL;
        $max = $this->offset['len'] - 262144 - 4;
        for ($start = $start['len'] * 9 + 262144; $start < $max; $start += 9)
        {
            $tmp = $this->index[$start] . $this->index[$start + 1] . $this->index[$start + 2] . $this->index[$start + 3];
            if ($tmp >= $nip2)
            {
                $off = unpack('Vlen', substr($this->index, $start + 4, 3) . "\x0");
                $len = unpack('nlen', $this->index[$start + 7] . $this->index[$start + 8]);
                break;
            }
        }
        if ($off === NULL)
        {
            return FALSE;
        }
        fseek($this->file, $this->offset['len'] + $off['len'] - 262144);
        return explode("\t", fread($this->file, $len['len']));
    }
}