<?php
  header('Content-type: text/json');
  $json = array();

  $dir_random = '../../data/' . $_REQUEST['exec']; 

  // Tamanho do arquivo para upload em MB
  $fileSizeMB = 20 ;

  if ( !(is_dir($dir_random)) ){
    mkdir( $dir_random , 0700 );
  }

  // Pasta onde o arquivo vai ser salvo
  $_UP['pasta'] = $dir_random . '/';
  
  // Tamanho máximo do arquivo (em Bytes)
  $_UP['tamanho'] = 1024 * 1024 * $fileSizeMB; // MB
  
  // Array com as extensões permitidas
  $_UP['extensoes'] = array('tsv' , 'gmt');
  
  // Renomeia o arquivo? (Se true, o arquivo será salvo como .tsv e um nome único)
  $_UP['renomeia'] = true;
  
  // Array com os tipos de erros de upload do PHP
  $_UP['erros'][0] = 'Não houve erro';
  $_UP['erros'][1] = 'O arquivo no upload é maior do que o limite do PHP';
  $_UP['erros'][2] = 'O arquivo ultrapassa o limite de tamanho especifiado no HTML';
  $_UP['erros'][3] = 'O upload do arquivo foi feito parcialmente';
  $_UP['erros'][4] = 'Não foi feito o upload do arquivo';
 
  /*
  EXPRESSIONDATA UPLOAD
  */
  if ( $_FILES['expressionData']['size'] != 0 ) {

    // Verifica se houve algum erro com o upload. Se sim, exibe a mensagem do erro
    if ($_FILES['expressionData']['error'] != 0) {
      $json['error'] = "Não foi possível fazer o upload, erro:" . $_UP['erros'][$_FILES['expressionData']['error']];
      echo json_encode($json);
      exit; // Para a execução do script
    }

    // Caso o script chegue a esse ponto, não houve erro com o upload e o PHP pode continuar
    // Faz a verificação da extensão do arquivo
    $preextensao = explode('.', $_FILES['expressionData']['name']); 
    // Se fizer tudo direto o php retorna um erro
    // PHP Notice:  Only variables should be passed by reference 
    $extensao = strtolower(end($preextensao));
    if (array_search($extensao, $_UP['extensoes']) === false) {
      $json['error'] = "Por favor, envie arquivos com as seguinte(s) extensõe(s): tsv";
      echo json_encode($json);
      exit;
    }

    // Faz a verificação do tamanho do arquivo
    if ($_FILES['expressionData']['size'] > $_UP['tamanho']){
      $json['error'] = "O arquivo enviado é muito grande, envie arquivos de até " . $fileSizeMB . ".";
      echo json_encode($json);
      exit; // Para a execução do script
    }

    // O arquivo passou em todas as verificações, hora de tentar movê-lo para a pasta
    // Primeiro verifica se deve trocar o nome do arquivo
    if ($_UP['renomeia'] == true) {
      // Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .tsv
      //$nome_final = md5(time()).'.tsv';
      $nome_final = 'edata.tsv';
    } else {
      // Mantém o nome original do arquivo
      $nome_final = $_FILES['expressionData']['name'];
    }
      
    // Depois verifica se é possível mover o arquivo para a pasta escolhida
    if (move_uploaded_file($_FILES['expressionData']['tmp_name'], $_UP['pasta'] . $nome_final)) {
      // Upload efetuado com sucesso
    } else {
      // Não foi possível fazer o upload, provavelmente a pasta está incorreta
      $json['error'] = "Não foi possível enviar o arquivo, tente novamente";
      echo json_encode($json);
      exit; // Para a execução do script
    }
  }

  /*
  PHENOTYPICDATA UPLOAD
  */
  if ( $_FILES['phenotypicData']['size'] != 0 ) {

    // Verifica se houve algum erro com o upload. Se sim, exibe a mensagem do erro
    if ($_FILES['phenotypicData']['error'] != 0) {
      $json['error'] = "Não foi possível fazer o upload, erro:" . $_UP['erros'][$_FILES['phenotypicData']['error']];
      echo json_encode($json);
      exit; // Para a execução do script
    }

    // Caso o script chegue a esse ponto, não houve erro com o upload e o PHP pode continuar
    // Faz a verificação da extensão do arquivo
    $preextensao = explode('.', $_FILES['phenotypicData']['name']); 
    // Se fizer tudo direto o php retorna um erro
    // PHP Notice:  Only variables should be passed by reference 
    $extensao = strtolower(end($preextensao));
    if (array_search($extensao, $_UP['extensoes']) === false) {
      $json['error'] = "Por favor, envie arquivos com as seguinte(s) extensõe(s): tsv";
      echo json_encode($json);
      exit;
    }

    // Faz a verificação do tamanho do arquivo
    if ($_FILES['phenotypicData']['size'] > $_UP['tamanho']) {
      $json['error'] = "O arquivo enviado é muito grande, envie arquivos de até " . $fileSizeMB . ".";
      echo json_encode($json);
      exit; // Para a execução do script
    }
    // O arquivo passou em todas as verificações, hora de tentar movê-lo para a pasta
    // Primeiro verifica se deve trocar o nome do arquivo
    if ($_UP['renomeia'] == true) {
      // Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .tsv
      //$nome_final = md5(time()).'.tsv';
      $nome_final = 'pdata.tsv';
    } else {
      // Mantém o nome original do arquivo
      $nome_final = $_FILES['phenotypicData']['name'];
    }
      
    // Depois verifica se é possível mover o arquivo para a pasta escolhida
    if (move_uploaded_file($_FILES['phenotypicData']['tmp_name'], $_UP['pasta'] . $nome_final)) {
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
  }

  /*
  PATHWAYS GMT FILE UPLOAD
  */
  if ( $_FILES['pathwaysGMT']['size'] != 0 ) {

    // Verifica se houve algum erro com o upload. Se sim, exibe a mensagem do erro
    if ($_FILES['pathwaysGMT']['error'] != 0) {
      $json['error'] = "Não foi possível fazer o upload, erro:" . $_UP['erros'][$_FILES['pathwaysGMT']['error']];
      echo json_encode($json);
      exit; // Para a execução do script
    }

    // Caso o script chegue a esse ponto, não houve erro com o upload e o PHP pode continuar
    // Faz a verificação da extensão do arquivo
    $preextensao = explode('.', $_FILES['pathwaysGMT']['name']); 
    // Se fizer tudo direto o php retorna um erro
    // PHP Notice:  Only variables should be passed by reference 
    $extensao = strtolower(end($preextensao));
    if (array_search($extensao, $_UP['extensoes']) === false) {
      $json['error'] = "Por favor, envie arquivos com as seguinte(s) extensõe(s): gmt";
      echo json_encode($json);
      exit;
    }

    // Faz a verificação do tamanho do arquivo
    if ($_FILES['pathwaysGMT']['size'] > $_UP['tamanho']) {
      $json['error'] = "O arquivo enviado é muito grande, envie arquivos de até " . $fileSizeMB . ".";
      echo json_encode($json);
      exit; // Para a execução do script
    }
    // O arquivo passou em todas as verificações, hora de tentar movê-lo para a pasta
    // Primeiro verifica se deve trocar o nome do arquivo
    if ($_UP['renomeia'] == true) {
      // Cria um nome baseado no UNIX TIMESTAMP atual e com extensão .tsv
      //$nome_final = md5(time()).'.tsv';
      $nome_final = 'pathways.gmt';
    } else {
      // Mantém o nome original do arquivo
      $nome_final = $_FILES['pathwaysGMT']['name'];
    }
      
    // Depois verifica se é possível mover o arquivo para a pasta escolhida
    if (move_uploaded_file($_FILES['pathwaysGMT']['tmp_name'], $_UP['pasta'] . $nome_final)) {
      // Upload efetuado com sucesso, exibe uma mensagem e um link para o arquivo
      $delm="\t";
      $colunaClass=0;

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
  }

?>