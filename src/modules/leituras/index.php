<?php
//2021.10.02.00
//Protocol Corporation Ltda.
//https://github.com/SantuarioMisericordiaRJ/StbModuleLeituras

require(__DIR__ . '/anoliturgico.php');

function AnoLetra(int $Ano = null):?string{
  if($Ano === null):
    $Ano = time();
  endif;
  $temp = date('Y', $Ano) % 3;
  if($temp === 0):
    return 'c';
  elseif($temp === 1):
    return 'a';
  elseif($temp === 2):
    return 'b';
  else:
    return null;
  endif;
}

function Command_leitura(){
  DebugTrace();
  global $Language, $Webhook;
  define('Url', 'https://raw.githubusercontent.com/SantuarioMisericordiaRJ/ApiCatolica/main/src');
  $AnoLiturgico = new AnoLiturgico();
  $Tempos = [
    AnoLiturgico::TempoComum => ['tc', 'Tempo comum'],
    AnoLiturgico::TempoAdvento => ['adv', 'Advento'],
    AnoLiturgico::TempoQuaresma => ['qrm', 'Quaresma'],
    AnoLiturgico::TempoNatal => ['ntl', 'Natal']
  ];
  $index = file_get_contents(Url . '/index.json');
  $index = json_decode($index, true);
  $datas = file_get_contents(Url . '/datas.json');
  $datas = json_decode($datas, true);
  $especiais = file_get_contents(Url . '/especiais.json');
  $especiais = json_decode($especiais, true);

  list($tempo, $semana) = $AnoLiturgico->TempoGet(time());
  $hoje = date('Y-m-d');
  $DiaSemana = date('N');
  if($DiaSemana === '7'):
    $ano = AnoLetra();
  elseif(date('Y') % 2 === 0):
    $ano = 'p';
  else:
    $ano = 'i';
  endif;

  if(isset($datas['all'][$hoje])):
    $especial = $especiais[$datas['all'][$hoje]];
    $l1 = $especial[1];
    $r = $especial['r'];
    $l2 = $especial[2] ?? null;
    $e = $especial['e'];
  else:
    $l1 = $index[$Tempos[$tempo][0]][$semana][$DiaSemana][$ano][1];
    $r = $index[$Tempos[$tempo][0]][$semana][$DiaSemana][$ano]['r'] ?? null;
    $l2 = $index[$Tempos[$tempo][0]][$semana][$DiaSemana][$ano][2] ?? null;
    $e = $e = $index[$Tempos[$tempo][0]][$semana][$DiaSemana][$ano]['e'] ?? $index[$Tempos[$tempo][0]][$semana][$DiaSemana]['e'];
  endif;

  $texto = '<b>' . $semana . 'ª semana do ' . $Tempos[$tempo][1] . ' - ' . $Language->TextGet('WeekDay' . $DiaSemana) . "</b>\n";
  if(isset($especial)):
    $texto .= '<b>' . $especial['nome'] . "</b>\n";
  endif;
  $texto .= '1ª leitura: ' . $l1 . "\n";
  $texto .= 'Responsório: ' . $r . "\n";
  if($l2 !== null):
    $texto .= '2ª leitura: ' . $l2 . "\n";
  endif;
  $texto .= 'Evangelho: ' . $e . "\n\n";
  $Webhook->ReplyMsg($texto, null, null, TblParse::Html);
  LogEvent('leitura');
}