<?php

namespace App\DTO;

class ReportApiDTO
{
    public $error;
    public $errores = [];

    public $datos_personales = [];
    public $hallazgos = [
        'altos' => [],
        'medios' => [],
        'bajos' => [],
        'infos' => [],
    ];

    public $procesos_legales = [];
    public $listas_restrictivas = [];
    public $conduccion = [];
    public $rnmc = [];
    public $otras_fuentes = [];

    public static function fromApiResponse(array $response)
    {
        $dto = new self();

        $dto->error = $response['error'] ?? false;
        $dto->errores = $response['errores'] ?? [];

        $dto->datos_personales = $response['persona'] ?? [];

        if (isset($response['dict_hallazgos'])) {
            $dto->hallazgos = [
                'altos'  => $response['dict_hallazgos']['altos']  ?? [],
                'medios' => $response['dict_hallazgos']['medios'] ?? [],
                'bajos'  => $response['dict_hallazgos']['bajos']  ?? [],
                'infos'  => $response['dict_hallazgos']['infos']  ?? [],
            ];
        }

        $dto->procesos_legales = [
            'procuraduria'  => $response['procuraduria'] ?? [],
            'policia'       => $response['policia'] ?? [],
            'contraloria'   => $response['contraloria'] ?? [],
            'contaduria'    => $response['contaduria'] ?? [],
            'rama_unificada'=> $response['rama_unificada'] ?? [],
            'juzgados_tyba' => $response['juzgados_tyba'] ?? [],
            'inpec'         => $response['inpec'] ?? [],
            'rnmc'          => $response['rnmc'] ?? [],
        ];

        $dto->listas_restrictivas = [
            'ofac' => $response['OFAC'] ?? [],
            'onu'  => $response['ONU'] ?? [],
            'peps' => $response['PEPS'] ?? [],
        ];

        $dto->conduccion = [
            'rndc'  => $response['RNDC'] ?? [],
            'runt'  => $response['RUNT'] ?? [],
            'simit' => $response['SIMIT'] ?? [],
        ];

        $dto->otras_fuentes = [
            'cidob'             => $response['CIDOB'] ?? [],
            'fopep'             => $response['FOPEP'] ?? [],
            'libretamilitar'    => $response['LIBRETAMILITAR'] ?? [],
            'google'            => $response['GOOGLE'] ?? [],
            'garantias_mob'     => $response['garantias_mobiliarias'] ?? [],
            'inmov_bog'         => $response['inmov_bog'] ?? [],
        ];

        return $dto;
    }

    public function toArray()
    {
        return [
            'error' => $this->error,
            'errores' => $this->errores,
            'datos_personales' => $this->datos_personales,
            'hallazgos' => $this->hallazgos,
            'procesos_legales' => $this->procesos_legales,
            'listas_restrictivas' => $this->listas_restrictivas,
            'conduccion' => $this->conduccion,
            'rnmc' => $this->rnmc,
            'otras_fuentes' => $this->otras_fuentes,
        ];
    }
}
