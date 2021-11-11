<?php

namespace Api\Library\Util;

use \Exception;

class ParametersHelper
{
    /**
     * Valida se os campos obrigatórios estão definidos e se possuem valor
     *
     * @param array|\stdClass $params
     * @param array $mandatories
     * @throws \Exception
     */
    public static function validateMandatory($params, array $mandatories): void
    {
        if ($params instanceof \stdClass) {
            $params = json_decode(json_encode($params), true);
        }
        
        $erros = [];
        foreach ($mandatories as $mandatory) {
            if (!array_key_exists($mandatory, $params)) {
                $erros[] = "The mandatory param '{$mandatory}' was not sent";
            } else if (empty($params[$mandatory])
                && $params[$mandatory] !== 0
                ) {
                    $erros[] = "The mandatory param '{$mandatory}' has no value";
                }
        }
        
        if (count($erros)) {
            throw new Exception(implode('. ', $erros), 400);
        }
    }
}
