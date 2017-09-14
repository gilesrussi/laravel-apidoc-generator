<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 14/09/17
 * Time: 14:40
 */

namespace Mpociot\ApiDoc\Parsers;

use Faker\Factory;
use Mpociot\ApiDoc\Parsers\RuleDescriptionParser as Description;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class RuleParser
{

    public function parse($rule, $ruleName, &$attributeData, $seed) {
        $faker = Factory::create();
        $faker->seed(crc32($seed));

        $parsedRule = $this->parseStringRule($rule);
        $parsedRule[0] = $this->normalizeRule($parsedRule[0]);
        list($rule, $parameters) = $parsedRule;

        switch ($rule) {
            case 'required':
                $attributeData['required'] = true;
                break;
            case 'accepted':
                $attributeData['required'] = true;
                $attributeData['type'] = 'boolean';
                $attributeData['value'] = true;
                break;
            case 'after':
                $attributeData['type'] = 'date';
                $attributeData['description'][] = Description::parse($rule)->with(date(DATE_RFC850, strtotime($parameters[0])))->getDescription();
                $attributeData['value'] = date(DATE_RFC850, strtotime('+1 day', strtotime($parameters[0])));
                break;
            case 'alpha':
                $attributeData['description'][] = Description::parse($rule)->getDescription();
                $attributeData['value'] = $faker->word;
                break;
            case 'alpha_dash':
                $attributeData['description'][] = Description::parse($rule)->getDescription();
                break;
            case 'alpha_num':
                $attributeData['description'][] = Description::parse($rule)->getDescription();
                break;
            case 'in':
                $attributeData['description'][] = Description::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                $attributeData['value'] = $faker->randomElement($parameters);
                break;
            case 'not_in':
                $attributeData['description'][] = Description::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                $attributeData['value'] = $faker->word;
                break;
            case 'min':
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                if (Arr::get($attributeData, 'type') === 'numeric' || Arr::get($attributeData, 'type') === 'integer') {
                    $attributeData['value'] = $faker->numberBetween($parameters[0]);
                }
                break;
            case 'max':
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                if (Arr::get($attributeData, 'type') === 'numeric' || Arr::get($attributeData, 'type') === 'integer') {
                    $attributeData['value'] = $faker->numberBetween(0, $parameters[0]);
                }
                break;
            case 'between':
                if (! isset($attributeData['type'])) {
                    $attributeData['type'] = 'numeric';
                }
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                $attributeData['value'] = $faker->numberBetween($parameters[0], $parameters[1]);
                break;
            case 'before':
                $attributeData['type'] = 'date';
                $attributeData['description'][] = Description::parse($rule)->with(date(DATE_RFC850, strtotime($parameters[0])))->getDescription();
                $attributeData['value'] = date(DATE_RFC850, strtotime('-1 day', strtotime($parameters[0])));
                break;
            case 'date_format':
                $attributeData['type'] = 'date';
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                $attributeData['value'] = date($parameters[0]);
                break;
            case 'different':
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                break;
            case 'digits':
                $attributeData['type'] = 'numeric';
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                $attributeData['value'] = ($parameters[0] < 9) ? $faker->randomNumber($parameters[0], true) : substr(mt_rand(100000000, mt_getrandmax()), 0, $parameters[0]);
                break;
            case 'digits_between':
                $attributeData['type'] = 'numeric';
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                break;
            case 'file':
                $attributeData['type'] = 'file';
                $attributeData['description'][] = Description::parse($rule)->getDescription();
                break;
            case 'image':
                $attributeData['type'] = 'image';
                $attributeData['description'][] = Description::parse($rule)->getDescription();
                break;
            case 'json':
                $attributeData['type'] = 'string';
                $attributeData['description'][] = Description::parse($rule)->getDescription();
                $attributeData['value'] = json_encode(['foo', 'bar', 'baz']);
                break;
            case 'mimetypes':
            case 'mimes':
                $attributeData['description'][] = Description::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                break;
            case 'required_if':
                $attributeData['description'][] = Description::parse($rule)->with($this->splitValuePairs($parameters))->getDescription();
                break;
            case 'required_unless':
                $attributeData['description'][] = Description::parse($rule)->with($this->splitValuePairs($parameters))->getDescription();
                break;
            case 'required_with':
                $attributeData['description'][] = Description::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                break;
            case 'required_with_all':
                $attributeData['description'][] = Description::parse($rule)->with($this->fancyImplode($parameters, ', ', ' and '))->getDescription();
                break;
            case 'required_without':
                $attributeData['description'][] = Description::parse($rule)->with($this->fancyImplode($parameters, ', ', ' or '))->getDescription();
                break;
            case 'required_without_all':
                $attributeData['description'][] = Description::parse($rule)->with($this->fancyImplode($parameters, ', ', ' and '))->getDescription();
                break;
            case 'same':
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                break;
            case 'size':
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                break;
            case 'timezone':
                $attributeData['description'][] = Description::parse($rule)->getDescription();
                $attributeData['value'] = $faker->timezone;
                break;
            case 'exists':
                $fieldName = isset($parameters[1]) ? $parameters[1] : $ruleName;
                $attributeData['description'][] = Description::parse($rule)->with([Str::singular($parameters[0]), $fieldName])->getDescription();
                break;
            case 'active_url':
                $attributeData['type'] = 'url';
                $attributeData['value'] = $faker->url;
                break;
            case 'regex':
                $attributeData['type'] = 'string';
                $attributeData['description'][] = Description::parse($rule)->with($parameters)->getDescription();
                break;
            case 'boolean':
                $attributeData['value'] = true;
                $attributeData['type'] = $rule;
                break;
            case 'array':
                $attributeData['value'] = $faker->word;
                $attributeData['type'] = $rule;
                break;
            case 'date':
                $attributeData['value'] = $faker->date();
                $attributeData['type'] = $rule;
                break;
            case 'email':
                $attributeData['value'] = $faker->safeEmail;
                $attributeData['type'] = $rule;
                break;
            case 'string':
                $attributeData['value'] = $faker->word;
                $attributeData['type'] = $rule;
                break;
            case 'integer':
                $attributeData['value'] = $faker->randomNumber();
                $attributeData['type'] = $rule;
                break;
            case 'numeric':
                $attributeData['value'] = $faker->randomNumber();
                $attributeData['type'] = $rule;
                break;
            case 'url':
                $attributeData['value'] = $faker->url;
                $attributeData['type'] = $rule;
                break;
            case 'ip':
                $attributeData['value'] = $faker->ipv4;
                $attributeData['type'] = $rule;
                break;
            case 'custom':
                $attributeData['value'] = 'oi';
                $attributeData['type'] = $rule;

        }

        if ($attributeData['value'] === '') {
            $attributeData['value'] = $faker->word;
        }

        if (is_null($attributeData['type'])) {
            $attributeData['type'] = 'string';
        }
    }

    protected function parseStringRule($rules)
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Max:3" states that the value may only be three letters.
        if (strpos($rules, ':') !== false) {
            list($rules, $parameter) = explode(':', $rules, 2);

            $parameters = $this->parseParameters($rules, $parameter);
        }

        return [strtolower(trim($rules)), $parameters];
    }

    protected function parseParameters($rule, $parameter)
    {
        if (strtolower($rule) === 'regex') {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }

    protected function normalizeRule($rule)
    {
        switch ($rule) {
            case 'int':
                return 'integer';
            case 'bool':
                return 'boolean';
            default:
                return $rule;
        }
    }

    protected function splitValuePairs($parameters, $first = 'is ', $last = 'or ')
    {
        $attribute = '';
        collect($parameters)->map(function ($item, $key) use (&$attribute, $first, $last) {
            $attribute .= '`'.$item.'` ';
            if (($key + 1) % 2 === 0) {
                $attribute .= $last;
            } else {
                $attribute .= $first;
            }
        });
        $attribute = rtrim($attribute, $last);

        return $attribute;
    }

    protected function fancyImplode($arr, $first, $last)
    {
        $arr = array_map(function ($value) {
            return '`'.$value.'`';
        }, $arr);
        array_push($arr, implode($last, array_splice($arr, -2)));

        return implode($first, $arr);
    }
}