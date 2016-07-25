<?php

/*
* Arquivo que faz consultas ao banco de dados usando mysqli
* Autor: Felipe Samuel
* Link: www.fb.com/samukajobs
* Data: 24/07/2016
*/

function conecta(){
	//conecta com o banco de dados
	$link = @mysqli_connect(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD, DB_DATABASE) or die(mysqli_connect_error());
	//seta a codificação dos caracteres na comunicação com o banco de dados
	mysqli_set_charset($link,DB_CHARSET) or die(mysqli_error($link));

	//retorna a conexao, ou nao
	return $link;
}

function desconecta($link){
	//desconecta do banco de dados
	@mysqli_close($link) or die(mysqli_error($link));
}

//"proteje" contra sql injection
function proteje($dados){

	$link = conecta();

	if(!is_array($dados)){ // se não for um array
		$dados = mysqli_real_escape_string($link , $dados); //substitui aspas por caracteres de escape
	}else{
		$array = $dados;
		foreach ($array as $chave => $valor){ //percorre chaves e valores do array
			$chave = mysqli_real_escape_string($link , $chave); //substitui aspas por caracteres de escape
			$valor = mysqli_real_escape_string($link , $valor);

			$dados[$chave] = $valor; //remonta o array com os itens verificados

		}
	}

	desconecta($link);

	return $dados;

}

function insere($tabela,$dados, $retornaId = false){

	//pega as chaves do array e separa por virgula
	$campos = implode(', ', array_keys($dados));
	//pega os valores do array, acrescenta aspas simples e virgula na separação
	$valores = "'".implode("', '", $dados)."'";

	//cria a query
	$query = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";

	//retorna se inseriu ou nao
	return executa($query,$retornaId);
}

//retorna os registros da tabela
function pega($tabela, $parametros = null, $campos = '*'){
	//monta a query
	$query = "SELECT {$campos} FROM {$tabela} {$parametros}";
	//executa a query
	$result = executa($query);

	if(!mysqli_num_rows($result)){//se o numero de linhas de retorno for igual a 0...
		return false;
	}else{
		while($res = mysqli_fetch_assoc($result)){ //transorma os dados do bd em um array
			$dados[] = $res; // tribue os dados a outro array
		}

		return $dados;
	}
	return $result;
}

//faz uma atualizacao nos dados da tabela 
function atualiza($tabela, array $dados, $condicao = null, $retornaId = false){

	foreach ($dados as $chave => $valor) {//percorre o array recebido
		$campos[] = "{$chave} = '{$valor}'"; //atribui a chave do array(nome do campo no banco de dados) e concatena com o valor pra atualizar
	}

	$campos = implode(', ', $campos); //divide os campos por uma virgula

	$condicao = ($condicao) ? " WHERE {$condicao}" : null; //se existir uma condicao ele atribui, se nao ele modifica tudo da tabela

	$query = "UPDATE {$tabela} SET {$campos} {$condicao}"; //query usada pra atualizar os registros
	
	return executa($query, $retornaId);

}


//faz um delete na tabela
function deleta($tabela, $condicao = null, $retornaId = false){

	$condicao = ($condicao) ? " WHERE {$condicao}" : null; 
	$query = "DELETE FROM {$tabela} {$condicao}";

	return executa($query, $retornaId);
}

function executa($query, $retornaId =false){ // se o retornaId for passado como true, ele retorna o id do registro

	$link = conecta();
	$result = @mysqli_query($link, $query) or die(mysqli_error($link));

	if($retornaId)
		$result = mysqli_insert_id($link);

	desconecta($link);

	return $result;
}