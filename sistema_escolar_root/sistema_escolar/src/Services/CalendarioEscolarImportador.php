<?php

class CalendarioEscolarImportador
{
    private const PALAVRAS_CHAVE = [
        'feriado' => 'Feriado',
        'ponto facultativo' => 'Ponto facultativo',
        'recesso' => 'Recesso',
        'suspensao' => 'Suspensão',
        'suspensão' => 'Suspensão',
        'nao letivo' => 'Dia não letivo',
        'não letivo' => 'Dia não letivo',
        'conselho de classe' => 'Conselho de classe',
        'jornada pedagogica' => 'Jornada pedagógica',
        'jornada pedagógica' => 'Jornada pedagógica',
        'planejamento' => 'Planejamento',
    ];

    private const MESES = [
        'janeiro' => 1,
        'fevereiro' => 2,
        'marco' => 3,
        'março' => 3,
        'abril' => 4,
        'maio' => 5,
        'junho' => 6,
        'julho' => 7,
        'agosto' => 8,
        'setembro' => 9,
        'outubro' => 10,
        'novembro' => 11,
        'dezembro' => 12,
    ];

    public function importar($arquivo, $ano, $textoManual = '')
    {
        $ano = (int)$ano;
        $texto = trim((string)$textoManual);
        $avisos = [];

        if (!empty($arquivo['tmp_name']) && is_uploaded_file($arquivo['tmp_name'])) {
            if (!$this->arquivoPdfValido($arquivo)) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Envie um arquivo PDF válido.',
                    'avisos' => [],
                    'texto_extraido' => $texto
                ];
            }

            $textoPdf = $this->extrairTextoPdf($arquivo['tmp_name']);
            $texto = trim($texto . "\n" . $textoPdf);

            if (trim($textoPdf) === '') {
                $avisos[] = 'Não foi possível extrair texto do PDF. Se ele for imagem, cole o texto do calendário no campo de apoio.';
            }
        }

        if ($texto === '') {
            return [
                'sucesso' => false,
                'mensagem' => 'Envie um PDF com texto extraível ou cole o texto do calendário.',
                'avisos' => $avisos,
                'texto_extraido' => ''
            ];
        }

        $eventos = $this->identificarEventos($texto, $ano);

        return [
            'sucesso' => !empty($eventos),
            'mensagem' => empty($eventos)
                ? 'Nenhum evento foi identificado automaticamente. Revise o texto ou informe os itens manualmente.'
                : 'Revise os eventos encontrados antes de salvar na Agenda.',
            'avisos' => $avisos,
            'eventos' => $eventos,
            'texto_extraido' => $texto
        ];
    }

    private function arquivoPdfValido($arquivo)
    {
        $nome = strtolower((string)($arquivo['name'] ?? ''));
        $tipo = strtolower((string)($arquivo['type'] ?? ''));

        return str_ends_with($nome, '.pdf')
            && in_array($tipo, ['application/pdf', 'application/x-pdf', ''], true);
    }

    private function extrairTextoPdf($caminho)
    {
        $texto = $this->extrairComPdftotext($caminho);

        if (trim($texto) !== '') {
            return $texto;
        }

        return $this->extrairTextoBasicoPdf($caminho);
    }

    private function extrairComPdftotext($caminho)
    {
        if (!function_exists('shell_exec')) {
            return '';
        }

        $binario = $this->localizarPdftotext();

        if ($binario === '') {
            return '';
        }

        $redirecionarErro = DIRECTORY_SEPARATOR === '\\' ? ' 2>NUL' : ' 2>/dev/null';
        $comando = escapeshellarg($binario) . ' -layout -enc UTF-8 ' . escapeshellarg($caminho) . ' -' . $redirecionarErro;
        $saida = shell_exec($comando);

        return is_string($saida) ? $saida : '';
    }

    private function localizarPdftotext()
    {
        foreach (['pdftotext', '/usr/bin/pdftotext', '/usr/local/bin/pdftotext'] as $binario) {
            if ($binario !== 'pdftotext' && is_file($binario)) {
                return $binario;
            }
        }

        return 'pdftotext';
    }

    private function extrairTextoBasicoPdf($caminho)
    {
        $conteudo = @file_get_contents($caminho);

        if ($conteudo === false) {
            return '';
        }

        $texto = '';

        if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $conteudo, $streams)) {
            foreach ($streams[1] as $stream) {
                $decodificado = @gzuncompress(trim($stream));

                if ($decodificado === false) {
                    $decodificado = @gzdecode(trim($stream));
                }

                if ($decodificado !== false) {
                    $texto .= ' ' . $this->extrairStringsPdf($decodificado);
                }
            }
        }

        return trim($texto);
    }

    private function extrairStringsPdf($conteudo)
    {
        $texto = '';

        if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/s', $conteudo, $matches)) {
            foreach ($matches[0] as $literal) {
                $literal = substr($literal, 1, -1);
                $literal = preg_replace('/\\\\([\\\\()])/', '$1', $literal);
                $literal = preg_replace('/\\\\[nrtbf]/', ' ', $literal);
                $texto .= ' ' . $literal;
            }
        }

        return $texto;
    }

    private function identificarEventos($texto, $ano)
    {
        $linhas = preg_split('/\R+/', $texto) ?: [];
        $eventos = [];

        foreach ($linhas as $linha) {
            $linha = trim(preg_replace('/\s+/', ' ', $linha));

            if ($linha === '') {
                continue;
            }

            $tipo = $this->identificarTipo($linha);

            if ($tipo === null) {
                continue;
            }

            foreach ($this->extrairDatasLinha($linha, $ano) as $data) {
                $eventos[$data . '|' . $linha] = [
                    'data' => $data,
                    'titulo' => $this->montarTitulo($tipo, $linha),
                    'descricao' => $linha,
                    'tipo' => $tipo,
                ];
            }
        }

        usort($eventos, function ($a, $b) {
            return strcmp($a['data'], $b['data']);
        });

        return array_values($eventos);
    }

    private function identificarTipo($linha)
    {
        $normalizada = $this->normalizar($linha);

        foreach (self::PALAVRAS_CHAVE as $chave => $tipo) {
            if (str_contains($normalizada, $this->normalizar($chave))) {
                return $tipo;
            }
        }

        return null;
    }

    private function extrairDatasLinha($linha, $ano)
    {
        $datas = [];

        if (preg_match_all('/\b(\d{1,2})[\/.-](\d{1,2})(?:[\/.-](\d{2,4}))?\b/', $linha, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $anoData = !empty($match[3]) ? (int)$match[3] : $ano;
                $anoData = $anoData < 100 ? 2000 + $anoData : $anoData;
                $this->adicionarData($datas, $anoData, (int)$match[2], (int)$match[1]);
            }
        }

        foreach (self::MESES as $mesNome => $mesNumero) {
            $mesRegex = preg_quote($mesNome, '/');

            if (preg_match_all('/\b(\d{1,2})\s*(?:a|-|até)\s*(\d{1,2})\s*(?:de\s*)?' . $mesRegex . '\b/iu', $linha, $ranges, PREG_SET_ORDER)) {
                foreach ($ranges as $range) {
                    $inicio = (int)$range[1];
                    $fim = (int)$range[2];

                    if ($fim >= $inicio && ($fim - $inicio) <= 31) {
                        for ($dia = $inicio; $dia <= $fim; $dia++) {
                            $this->adicionarData($datas, $ano, $mesNumero, $dia);
                        }
                    }
                }
            }

            if (preg_match_all('/\b(\d{1,2})\s*(?:de\s*)?' . $mesRegex . '\b/iu', $linha, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $this->adicionarData($datas, $ano, $mesNumero, (int)$match[1]);
                }
            }
        }

        return array_values(array_unique($datas));
    }

    private function adicionarData(&$datas, $ano, $mes, $dia)
    {
        if (checkdate($mes, $dia, $ano)) {
            $datas[] = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
        }
    }

    private function montarTitulo($tipo, $linha)
    {
        $linhaLimpa = trim(preg_replace('/\s+/', ' ', $linha));
        $meses = implode('|', array_map('preg_quote', array_keys(self::MESES)));
        $linhaLimpa = preg_replace('/^\d{1,2}\s*(?:a|-|até)\s*\d{1,2}\s*(?:de\s*)?(?:' . $meses . ')\b/iu', '', $linhaLimpa);
        $linhaLimpa = preg_replace('/^\d{1,2}\s*(?:de\s*)?(?:' . $meses . ')\b/iu', '', $linhaLimpa);
        $linhaLimpa = preg_replace('/^[\d\s\/.\-–—]+/', '', $linhaLimpa);
        $linhaLimpa = trim($linhaLimpa, " -–—\t\n\r\0\x0B");

        if ($linhaLimpa === '' || mb_strlen($linhaLimpa, 'UTF-8') > 90) {
            return 'Calendário escolar - ' . $tipo;
        }

        return $linhaLimpa;
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
