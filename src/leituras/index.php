<?php
//2021.09.07.03
//Protocol Corporation Ltda.
https://github.com/SantuarioMisericordiaRJ/StbModuleLeituras

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
  global $Bot, $Language;
  $AnoLiturgico = new AnoLiturgico();
  $Tempos = [
    'tc' => 'Tempo comum',
    'adv' => 'Advento',
    'qrm' => 'Quaresma',
    'ntl' => 'Natal'
  ];
  $index = file_get_contents('https://raw.githubusercontent.com/SantuarioMisericordiaRJ/ApiCatolica/main/src/index-min.json');
  $especiais = file_get_contents('https://raw.githubusercontent.com/SantuarioMisericordiaRJ/ApiCatolica/main/src/especiais-min.json');
  $index = json_decode($index, true);
  $especiais = json_decode($especiais, true);
  $temp = $AnoLiturgico->TempoGet(time());
  $hoje = date('Y-m-d');

  if(isset($temp[0]) and $temp[0] === AnoLiturgico::TempoComum):
    $tempo = 'tc';
    $semana = $temp[1];
  endif;
  $DiaSemana = date('N');
  if($DiaSemana === '7'):
    $ano = AnoLetra();
  elseif(date('Y') % 2 === 0):
    $ano = 'p';
  else:
    $ano = 'i';
  endif;
  $l1 = $especiais[$hoje][1] ?? $index[$tempo][$semana][$DiaSemana][$ano][1];
  $r = $especiais[$hoje]['r'] ?? $index[$tempo][$semana][$DiaSemana][$ano]['r'];
  $l2 = $especiais[$hoje][2] ?? $index[$tempo][$semana][$DiaSemana][$ano][2] ?? null;
  //Evangelho: especial ou domingo ou dia de semana...
  $e = $especiais[$hoje]['e'] ?? $e = $index[$tempo][$semana][$DiaSemana][$ano]['e'] ?? $index[$tempo][$semana][$DiaSemana]['e'];

  $texto = '<b>' . $semana . 'ª semana do ' . $Tempos[$tempo] . ' - ' . $Language->TextGet('WeekDay' . $DiaSemana) . "</b>\n";
  if(isset($especiais[$hoje]['nome'])):
    $texto .= '<b>' . $especiais[$hoje]['nome'] . "</b>\n";
  endif;
  $texto .= '1ª leitura: ' . $l1 . "\n";
  $texto .= 'Responsório: ' . $r . "\n";
  if($l2 !== null):
    $texto .= '2ª leitura: ' . $l2 . "\n";
  endif;
  $texto .= 'Evangelho: ' . $e . "\n\n";
  $Bot->Send($Bot->ChatId(), $texto);
  LogEvent('leitura');
}