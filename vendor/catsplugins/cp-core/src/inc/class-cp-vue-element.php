<?php

namespace CastPlugin;

class CpVueElement
{
    public $data;

    function __construct($name, $data)
    {
        $def = [
            "type"=> "text",
            "label" => "",
        ];

        $data = array_merge($def, $data);
        $data['name'] = CpPageSetting::sanitizeKeyName($name);

        $this->data = $data;
    }

    public function render($echo = true)
    {
        $output = '';
        switch ($this->data['type']) {

            case "button-link":
                $output = $this->renderButtonLink();
                break;

            case "checkbox":
                $output = $this->renderCheckbox();
                break;

            case "text":
            default:
                $output = $this->renderText();
                break;

        }


        if (!$echo) return $output;

        echo $output;
    }

    private function renderCheckbox()
    {

        $output = "<el-form-item label=\"{$this->data['label']}\">";
        $output .= " <el-checkbox-group v-model='form.{$this->data['name']}'>";
        $output .= "<el-checkbox  name='{$this->data['name']}' label='{$this->data['text']}'></el-checkbox>";
        $output .=" </el-checkbox-group></el-form-item>";

        return $output;
    }
    private function renderText()
    {

        $output = "<el-form-item label=\"{$this->data['label']}\">
            <el-input name='{$this->data['name']}' v-model=\"form.{$this->data['name']}\"></el-input>
        </el-form-item>";

        return $output;
    }

    private function renderButtonLink()
    {
        $desc = '';
        if (isset($this->data['desc']) && !empty($this->data['desc'])) {
            $desc = "<div><i>{$this->data['desc']}</i></div>";
        }

        $type = (isset($this->data['type_button']) && !empty($this->data['type_button'])) ? $this->data['type_button'] : 'default';
        $text = (isset($this->data['text_button']) && !empty($this->data['text_button'])) ? $this->data['text_button'] : $this->data['label'];

        if (isset($this->data['link_filter']) && !empty($this->data['link_filter'])){
            $link = apply_filters($this->data['link_filter'], '');
        } else {
            $link = (isset($this->data['link']) && !empty($this->data['link'])) ? $this->data['link'] : '';
        }

        $output = "<el-form-item label=\"{$this->data['label']}\">
            <el-button @click='redirect(\"{$link}\")' type=\"{$type}\">{$text}</el-button>
            {$desc}
        </el-form-item>";

        return $output;
    }
}