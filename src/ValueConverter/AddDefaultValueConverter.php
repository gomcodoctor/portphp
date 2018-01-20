<?php

namespace Gomcodoctor\Portphp\ValueConverter;

/**
 * Converts a nested array using a converter-map
 *
 * @author Christoph Rosse <christoph@rosse.at>
 */
class AddDefaultValueConverter
{
    /**
     * @var array
     */
    private $fields;


    /**
     * @param callable[] $converters
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;

    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($input)
    {
        if (!is_array($input)) {
            throw new \InvalidArgumentException('Input of a ArrayValueConverterMap must be an array');
        }

        $input = $this->mergeDefaultValues($input);

        return $input;
    }

    public function mergeDefaultValues($item){
        foreach($this->fields as $key => $value){
            if(isset($item[$key])){
                if(empty($item[$key])) $item[$key] = $value;
            }
            else $item[$key] =$value;
        }
        return $item;

    }

}
