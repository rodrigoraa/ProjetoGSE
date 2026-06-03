<?php

class FeriadosService
{
    public function listarPorAno($ano)
    {
        $ano = (int)$ano;

        return $this->removerDuplicados(array_merge(
            $this->listarNacionais($ano),
            $this->listarMunicipaisVicentina($ano),
            $this->listarDatasMoveisComplementares($ano)
        ));
    }

    public function listarProximos($dias)
    {
        $hoje = new DateTimeImmutable('today');
        $limite = $hoje->modify('+' . (int)$dias . ' days');
        $anos = [(int)$hoje->format('Y')];

        if ($limite->format('Y') !== $hoje->format('Y')) {
            $anos[] = (int)$limite->format('Y');
        }

        $feriados = [];

        foreach (array_unique($anos) as $ano) {
            $feriados = array_merge($feriados, $this->listarPorAno($ano));
        }

        $feriados = array_values(array_filter($feriados, function ($feriado) use ($hoje, $limite) {
            if (empty($feriado['date'])) {
                return false;
            }

            $data = DateTimeImmutable::createFromFormat('Y-m-d', $feriado['date']);

            return $data && $data >= $hoje && $data <= $limite;
        }));

        usort($feriados, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $feriados;
    }

    private function listarNacionais($ano)
    {
        $chave = "feriados_$ano";

        if (!isset($_SESSION[$chave])) {
            $jsonFeriados = @file_get_contents("https://brasilapi.com.br/api/feriados/v1/{$ano}");
            $_SESSION[$chave] = $jsonFeriados ? json_decode($jsonFeriados, true) : [];
        }

        return array_map(function ($feriado) {
            return [
                'date' => $feriado['date'] ?? '',
                'name' => $feriado['name'] ?? 'Feriado Nacional',
                'type' => 'nacional'
            ];
        }, $_SESSION[$chave]);
    }

    private function listarMunicipaisVicentina($ano)
    {
        $feriados = [
            ['date' => sprintf('%04d-05-25', $ano), 'name' => 'Feriado Municipal de Vicentina'],
            ['date' => sprintf('%04d-06-20', $ano), 'name' => 'Aniversário de Vicentina'],
            ['date' => sprintf('%04d-09-12', $ano), 'name' => 'Morte do Padre Roberto'],
            ['date' => sprintf('%04d-10-01', $ano), 'name' => 'Santa Terezinha'],
            ['date' => sprintf('%04d-12-08', $ano), 'name' => 'Morte do Padre José Daniel'],
        ];

        return array_map(function ($feriado) {
            return [
                'date' => $feriado['date'],
                'name' => $feriado['name'],
                'type' => 'municipal'
            ];
        }, $feriados);
    }

    private function listarDatasMoveisComplementares($ano)
    {
        $pascoa = $this->calcularPascoa($ano);

        return [
            [
                'date' => $pascoa->modify('+60 days')->format('Y-m-d'),
                'name' => 'Corpus Christi',
                'type' => 'ponto_facultativo'
            ],
        ];
    }

    private function calcularPascoa($ano)
    {
        $a = $ano % 19;
        $b = intdiv($ano, 100);
        $c = $ano % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $ano, $mes, $dia));
    }

    private function removerDuplicados($feriados)
    {
        $unicos = [];

        foreach ($feriados as $feriado) {
            if (empty($feriado['date']) || empty($feriado['name'])) {
                continue;
            }

            $chave = $feriado['date'] . '|' . $this->normalizar($feriado['name']);
            $unicos[$chave] = $feriado;
        }

        usort($unicos, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return array_values($unicos);
    }

    private function normalizar($texto)
    {
        $texto = mb_strtolower((string)$texto, 'UTF-8');
        $mapa = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'õ' => 'o', 'ô' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ];

        return strtr($texto, $mapa);
    }
}
