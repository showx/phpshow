<?php
namespace phpshow\helper;
/**
 * 简单验证
 * Author:show
 */
class validation
{
    public function __construct($input, $rules)
    {
        $this->input = $input;
        $this->rules = $rules;
        $this->parseRules();
        return $this->check();
    }
    
    private function check()
    {
        foreach ($this->rules as $key => $value) {
            $this->results[$key] = array();
            
            foreach ($this->rules[$key] as $rule) {
                if (isset($this->input[$key]) && $this->input[$key] !== '') {
                    $method = 'test_' . $rule;
                    $result = $this->callMethod($method, $this->input[$key]);
                    $this->results[$key][$rule] = $result;
                } elseif ($rule === 'required') {
                    $this->results[$key][$rule] = 'required';
                }
            }
        }
        $this->errors = $this->errors();
        return $this->results;
    }
    
    public function passed()
    {
        return empty($this->errors);
    }
    
    public function failed()
    {
        return !empty($this->errors);
    }
    
    private function errors()
    {
        foreach ($this->results as $key => $result) {
            $unset = true;
            foreach ($result as $k => $v) {
                if ($v == 1) {
                    unset($this->results[$key][$k]);
                } else {
                    if (strpos($k, ':') !== false) {
                        $array = explode(':', $k);
                        unset($this->results[$key][$k]);
                        $this->results[$key][$array[0]] = isset($array[1]) ? $array[1] : 1;
                    }
                    $unset = false;
                }
            }
            
            if ($unset) {
                unset($this->results[$key]);
            }
        }
        return $this->results;
    }
    
    private function parseRules()
    {
        foreach ($this->rules as $key => $rule) {
            $this->rules[$key] = explode('|', $rule);
        }
    }
    
    private function callMethod($method, $params)
    {
        if (method_exists($this, $method)) {
            return $this->$method($params);
        } elseif (strpos($method, ':') !== false) {
            $array = explode(':', $method);
            
            $call_method = isset($array[0]) ? $array[0] : false;
            $call_params = isset($array[1]) ? $array[1] : false;
            
            if ($call_method && $call_params && method_exists($this, $call_method)) {
                return $this->$call_method($params, $call_params);
            }
        }
        return false;
    }
    
    private function test_required($value)
    {
        return strlen($value) > 0;
    }
    
    private function test_number($value)
    {
        # swap commas for decimal point (german)
        $value = str_replace(',', '.', $value);
        return is_numeric($value);
    }
    
    private function test_boolean($value)
    {
        return is_bool($value) || in_array($value, array(0, 1));
    }
    
    private function test_max($value, $limit)
    {
        return strlen($value) <= $limit;
    }
    
    private function test_min($value, $limit)
    {
        return strlen($value) >= $limit;
    }
    
    private function test_iban($value)
    {
        $iban = false;
        $value = strtoupper($value);
        
        if (preg_match('/^[A-Z]{2}[A-Z0-9]{20}$/', $value) && strlen($value) === 22) {
            $number = str_replace(
                array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'),
                array(10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35), (substr($value, 4, 22) . substr($value, 0, 4))
            );
            
            $iban = (1 == bcmod($number, 97)) ? true : false;
        }
        
        return $iban;
    }
    
    private function test_bic($value)
    {
        return preg_match('/^[a-z]{6}[0-9a-z]{2}([0-9a-z]{3})?\z/i', $value);
    }
    
    private function test_alphanum($value)
    {
        return preg_match('/^[0-9a-zA-ZäöüÄÖÜß,.()-\s]+$/', $value);
    }
    
    private function test_telephone($value)
    {
        return preg_match('/^[0-9\/+\()]{6,25}+$/', $value);
    }
    
    private function test_date($value)
    {
        $date = date_parse($value);
        return $date['year'] && $date['month'] && $date['day'];
    }
    
    private function test_email($value)
    {
        return (strlen($value) > 5 && strlen($value) <= 50) && (strpos($value, '@') !== false && strpos($value, '.') !== false);
    }
    
    private function test_same_as($value, $second_value)
    {
        return $value === $second_value;
    }
    
    private function test_in($value, $list)
    {
        return in_array($value, explode(',', $list));
    }
}
