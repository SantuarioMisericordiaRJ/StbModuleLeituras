<?php
//2021.10.25.00
//Protocol Corporation Ltda.
//https://github.com/SantuarioMisericordiaRJ/StbModuleLeituras

require(__DIR__ . '/anoliturgico.php');
const LeituraUrl = 'https://raw.githubusercontent.com/SantuarioMisericordiaRJ/ApiCatolica/main/src';
const LeituraTempos = [
  AnoLiturgico::TempoComum => ['tc', 'Tempo comum'],
  AnoLiturgico::TempoAdvento => ['adv', 'Advento'],
  AnoLiturgico::TempoQuaresma => ['qrm', 'Quaresma'],
  AnoLiturgico::TempoNatal => ['ntl', 'Natal']
];

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
  $AnoLiturgico = new AnoLiturgico();
  $index = file_get_contents(LeituraUrl . '/index.json');
  $index = json_decode($index, true);
  $datas = file_get_contents(LeituraUrl . '/datas.json');
  $datas = json_decode($datas, true);
  $especiais = file_get_contents(LeituraUrl . '/especiais.json');
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
  endif;
  $l1 = $especial[1]
    ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano][1];
  $r = $especial['r']
    ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano]['r']
    ?? null;
  $l2 = $especial[2]
    ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano][2]
    ?? null;
  $e = $especial['e']
    ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano]['e']
    ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana]['e'];

  $texto = '<b>' . $semana . 'ª semana do ' . LeituraTempos[$tempo][1] . ' - ' . $Language->TextGet('WeekDay' . $DiaSemana) . "</b>\n";
  if(isset($datas['all'][$hoje])):
    $texto .= '<b>' . $especiais[$datas['all'][$hoje]]['nome'] . "</b>\n";
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

function Command_responsorio():void{
  DebugTrace();
  global $Webhook;
  $AnoLiturgico = new AnoLiturgico();
  $index = file_get_contents(LeituraUrl . '/index.json');
  $index = json_decode($index, true);
  $datas = file_get_contents(LeituraUrl . '/datas.json');
  $datas = json_decode($datas, true);
  $especiais = file_get_contents(LeituraUrl . '/especiais.json');
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
    $r = $especiais[$datas['all'][$hoje]]['r'];
    $rt = $especiais[$datas['all'][$hoje]]['rt'];
  else:
    $r = $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano]['r'];
    $rt = $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano]['rt'];
  endif;
  $rt = file_get_contents(LeituraUrl . '/salmos/' . $rt . '.json');
  $rt = json_decode($rt, true);

  foreach($rt as $index => $salmo):
    if($index === 0):
      $texto = 'Responsório (' . $r . ")\n\n";
      $texto .= '<b>' . $salmo . "</b>\n\n";
    else:
      $texto .= $index . ') ' . $salmo . "\n\n";
    endif;
  endforeach;
  $Webhook->ReplyMsg(
    $texto,
    null,
    null,
    TblParse::Html
  );
  LogEvent('leitura');
}