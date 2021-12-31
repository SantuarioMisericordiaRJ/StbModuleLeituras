<?php
//2021.12.31.00
//Protocol Corporation Ltda.
//https://github.com/SantuarioMisericordiaRJ/StbModuleLeituras

require(__DIR__ . '/anoliturgico.php');
require(__DIR__ . '/funcoes.php');
const LeituraUrl = 'https://raw.githubusercontent.com/SantuarioMisericordiaRJ/ApiCatolica/main/src';
const LeituraTempos = [
  AnoLiturgico::TempoComum => ['tc', 'Tempo comum'],
  AnoLiturgico::TempoAdvento => ['adv', 'Advento'],
  AnoLiturgico::TempoQuaresma => ['qrm', 'Quaresma'],
  AnoLiturgico::TempoNatal => ['ntl', 'Natal']
];

function Command_leitura(){
  DebugTrace();
  global $Language, $Webhook;
  
  $AnoLiturgico = new AnoLiturgico();
  $temp = $AnoLiturgico->TemposGet();
  if(time() > $temp[AnoLiturgico::TempoComum][34]):
    $AnoLiturgico = new AnoLiturgico(strtotime('+1 year'));
  endif;

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
    $ano = AnoLetra(time(), $tempo);
  elseif(date('Y') % 2 === 0):
    $ano = 'p';
  else:
    $ano = 'i';
  endif;

  if(isset($datas['all'][$hoje])):
    $especial = $especiais[$datas['all'][$hoje]];
  endif;
  //Tempo do advento não tem ano par e impar
  if($tempo === AnoLiturgico::TempoAdvento and $DiaSemana < 7):
    $l1 = $especial[1]
      ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][1];
    $r = $especial['r']
      ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana]['r']
      ?? null;
    $l2 = $especial[2]
      ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][2]
      ?? null;
    $e = $especial['e']
      ?? $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana]['e'];
  elseif($tempo === AnoLiturgico::TempoNatal and $semana === 1 and $DiaSemana === '7'):
    $l1 = $index[LeituraTempos[$tempo][0]]['sgf'][1];
    $r = $index[LeituraTempos[$tempo][0]]['sgf']['r'];
    $l2 = $index[LeituraTempos[$tempo][0]]['sgf'][2];
    $e = $index[LeituraTempos[$tempo][0]]['sgf']['e'];
  elseif(date('n') == 12 and date('j') >= 26 and date('j') < 32):
    $l1 = $index[LeituraTempos[$tempo][0]][date('j')][1];
    $r = $index[LeituraTempos[$tempo][0]][date('j')]['r'];
    $l2 = null;
    $e = $index[LeituraTempos[$tempo][0]][date('j')]['e'];
  else:
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
  endif;

  if($tempo === AnoLiturgico::TempoNatal and $semana === 1 and $DiaSemana < '7'):
    $texto = '<b>Oitava do Natal - ' . $Language->TextGet('WeekDay' . $DiaSemana) . "</b>\n";
  else:
    $texto = '<b>' . $semana . 'ª semana do ' . LeituraTempos[$tempo][1] . ' - ' . $Language->TextGet('WeekDay' . $DiaSemana) . "</b>\n";
  endif;
  if($tempo === AnoLiturgico::TempoNatal and $semana === 1 and $DiaSemana === '7'):
    $texto .= "<b>Sagrada família</b>\n";
  elseif(isset($datas['all'][$hoje])):
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
  $temp = $AnoLiturgico->TemposGet();
  if(time() > $temp[AnoLiturgico::TempoComum][34]):
    $AnoLiturgico = new AnoLiturgico(strtotime('+1 year'));
  endif;
  
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
    $ano = AnoLetra(time(), $tempo);
  elseif(date('Y') % 2 === 0):
    $ano = 'p';
  else:
    $ano = 'i';
  endif;

  if(isset($datas['all'][$hoje])):
    $r = $especiais[$datas['all'][$hoje]]['r'];
    $rt = $especiais[$datas['all'][$hoje]]['rt'];
  elseif($tempo === AnoLiturgico::TempoAdvento and $DiaSemana < 7):
    $r = $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana]['r'];
    $rt = $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana]['rt'];
  elseif($tempo === AnoLiturgico::TempoNatal and $semana === 1 and $DiaSemana === '7'):
    $r = $index[LeituraTempos[$tempo][0]]['sgf']['r'];
    $rt = $index[LeituraTempos[$tempo][0]]['sgf']['rt'];
  elseif(date('n') == 12 and date('j') >= 26 and date('j') < 32):
    $r = $index[LeituraTempos[$tempo][0]][date('j')]['r'];
    $rt = $index[LeituraTempos[$tempo][0]][date('j')]['rt'];
  else:
    $r = $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano]['r'];
    $rt = $index[LeituraTempos[$tempo][0]][$semana][$DiaSemana][$ano]['rt'];
  endif;
  $rt = file_get_contents(LeituraUrl . '/responsorios/' . $rt . '.json');
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