<?php
header('Content-type: text/json');
$json = array();
// Pasta onde o arquivo vai ser salvo
$_UP['pasta'] = 'uploads/';
// Tamanho máximo do arquivo (em Bytes)
$_UP['tamanho'] = 1024 * 1024 * 2; // 2Mb
// Array com as extensões permitidas
$_UP['extensoes'] = array('tsv');
// Renomeia o arquivo? (Se true, o arquivo será salvo como .tsv e um nome único)
$_UP['renomeia'] = false;
// Array com os tipos de erros de upload do PHP
$_UP['erros'][0] = 'Não houve erro';
$_UP['erros'][1] = 'O arquivo no upload é maior do que o limite do PHP';
$_UP['erros'][2] = 'O arquivo ultrapassa o limite de tamanho especifiado no HTML';
$_UP['erros'][3] = 'O upload do arquivo foi feito parcialmente';
$_UP['erros'][4] = 'Não foi feito o upload do arquivo';
// Verifica se houve algum erro com o upload. Se sim, exibe a mensagem do erro
if ($_FILES['arquivo']['error'] != 0) {
  $json['error'] = "Não foi possível fazer o upload, erro:" . $_UP['erros'][$_FILES['arquivo']['error']];
  echo json_encode($json);
  exit; // Para a execução do script
}

// Caso script chegue a esse ponto, não houve erro com o upload e o PHP pode continuar
// Faz a verificação da extensão do arquivo
$preextensao = explode('.', $_FILES['arquivo']['name']); 
// Se fizer tudo direto o php retorna um erro
// PHP Notice:  Only variables should be passed by reference 
$extensao = strtolower(end($preextensao));
if (array_search($extensao, $_UP['extensoes']) === false) {
  $json['error'] = "Por favor, envie arquivos com as seguinte(s) extensõe(s): tsv";
  echo json_encode($json);
  exit;
}

// Faz a verificação do tamanho do arquivo
if ($_UP['tamanho'] < $_FILES['arquivo']['size']) {
  $json['error'] = "O arquivo enviado é muito grande, envie arquivos de até 2Mb.";
  echo json_encode($json);
  exit; // Para a execução do script
}
// O arquivo passou em todas as verificações, hora de tentar movê-lo para a pasta
// Primeiro verifica se deve trocar o nome do arquivo
if ($_UP['renomeia'] == true) {
  // Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .tsv
  $nome_final = md5(time()).'.tsv';
} else {
  // Mantém o nome original do arquivo
  $nome_final = $_FILES['arquivo']['name'];
}
  
// Depois verifica se é possível mover o arquivo para a pasta escolhida
if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $_UP['pasta'] . $nome_final)) {
  // Upload efetuado com sucesso, exibe uma mensagem e um link para o arquivo
  $delm="\t";
  $colunaClass=1;

  $arquivo = fopen($_UP['pasta'] . $nome_final, "r");

  if ($arquivo) {
    
    while(!feof($arquivo)){ 
      $linhas[] = explode($delm, fgets($arquivo));
    }

    fclose($arquivo);
      
    unset($linhas[0]);
    unset($linhas[count($linhas)]);

    foreach($linhas as $elemento){
      $arrayClass_before[] = $elemento[$colunaClass];
    }

    // Remove duplicates class and organize id values 
    $arrayClass = array_values(array_unique($arrayClass_before));

    $json['classes'] = array();
    // Show class
    foreach ($arrayClass as $item) {
      $json['classes'][] = $item;
    }

    echo json_encode($json);

  }

} else {
  // Não foi possível fazer o upload, provavelmente a pasta está incorreta
  $json['error'] = "Não foi possível enviar o arquivo, tente novamente";
  echo json_encode($json);
  exit; // Para a execução do script
}
