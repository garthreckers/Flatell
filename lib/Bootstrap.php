<?php
namespace Spinion;

class Bootstrap
{
    public function execute($__file__, $__namespace__, $__class__)
    {
        foreach (glob(dirname($__file__) . '/*.php') as $file) {
            $class_name = basename($file, '.php');
            $class = $__namespace__ . '\\' . $class_name;

            if ((!empty($this->exclude) && in_array($class, $this->exclude)) || $class == $__class__) {
                continue;
            }

            $init = new $class();
        }
    }
}
