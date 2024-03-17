<?php

namespace I18n;

class YamlFileParser
{
    public function parse($file)
    {
        return $this->convert(yaml_parse_file(__DIR__ . '/../../resources/' . $file));
    }

    protected function convert($data)
    {
        $new_data = [];
        foreach ($data as $key1 => $val1) {
            if (!is_array($val1)) {
                $new_data[$key1] = $val1;
            } else {
                $val1 = $this->convert($val1);
                foreach ($val1 as $key2 => $val2) {
                    $new_data[$key1 . '.' . $key2] = $val2;
                }
            }
        }
        return $new_data;
    }
}
