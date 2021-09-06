<?php
//2021.09.05.00
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
  $index = json_decode($index, true);
  $temp = $AnoLiturgico->TempoGet(time());
  if(strpos($temp, 'TempoComum') === 0):
    $tempo = 'tc';
    $semana = substr($temp, 10);
  endif;
  $DiaSemana = date('N');
  if($DiaSemana === '7'):
    $ano = AnoLetra();
  else:
    if(date('Y') % 2 === 0):
      $ano = 'p';
    else:
      $ano = 'i';
    endif;
  endif;
  $texto = '<b>' . $semana . 'ª semana do ' . $Tempos[$tempo] . ' - ' . $Language->TextGet('WeekDay' . $DiaSemana) . "</b>\n";
  $texto .= '1ª leitura: ' . $index[$tempo][$semana][$DiaSemana][$ano][1] . "\n";
  $texto .= 'Responsório: ' . $index[$tempo][$semana][$DiaSemana][$ano]['r'] . "\n";
  if($DiaSemana === '7'):
    $texto .= '2ª leitura: ' . $index[$tempo][$semana][$DiaSemana][$ano][2] . "\n";
    $texto .= 'Evangelho: ' . $index[$tempo][$semana][$DiaSemana][$ano]['e'] . "\n\n";
  else:
    $texto .= 'Evangelho: ' . $index[$tempo][$semana][$DiaSemana]['e'] . "\n\n";
  endif;
  $Bot->Send($Bot->ChatId(), $texto);
  LogEvent('leitura');
}