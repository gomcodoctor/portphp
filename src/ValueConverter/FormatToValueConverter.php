<?php

namespace Gomcodoctor\Portphp\ValueConverter;

/**
 * Converts a nested array using a converter-map
 *
 * @author Christoph Rosse <christoph@rosse.at>
 */
class FormatToValueConverter
{
    /**
     * @var array
     */
    private $fields;

    /** @var  string */
    private $format;


    /** @var  string */
    private $fieldName;

    /**
     * @param callable[] $converters
     */
    public function __construct($fieldName, $format, array $fields)
    {
        $this->fields = $fields;
        $this->format = $format;
        $this->fieldName = $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($input)
    {
        if (!is_array($input)) {
            throw new \InvalidArgumentException('Input of a ArrayValueConverterMap must be an array');
        }

        if(isset($input[$this->fieldName])){
            if(!empty($input[$this->fieldName])) return $input;
        }

        $arg []= $this->format;

        foreach ($this->fields as $key){
            if(isset($input[$key])) $arg []= $input[$key];
            else throw new \InvalidArgumentException("$key is missing");
        }

        $input[$this->fieldName] = call_user_func_array('sprintf', $arg);

        return $input;
    }

}
