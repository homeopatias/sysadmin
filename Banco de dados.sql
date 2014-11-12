-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 12, 2014 at 11:36 AM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `homeopatias`
--

-- --------------------------------------------------------

--
-- Table structure for table `Administrador`
--

CREATE TABLE IF NOT EXISTS `Administrador` (
  `idAdmin` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico do administrador',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse administrador representa',
  `nivel` enum('professor','coordenador','administrador') NOT NULL COMMENT 'Nivel de privilegio desse administrador no sistema',
  `corrigeTrabalho` tinyint(1) NOT NULL COMMENT 'Caso esse administrador seja um professor, determina se ele pode corrigir trabalhos ou não',
  `permissoes` int(5) NOT NULL DEFAULT '0' COMMENT 'Bitflag de acesso de admins',
  PRIMARY KEY (`idAdmin`),
  KEY `idUsuario` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Administradores do sistema' AUTO_INCREMENT=8 ;

--
-- Dumping data for table `Administrador`
--

INSERT INTO `Administrador` (`idAdmin`, `idUsuario`, `nivel`, `corrigeTrabalho`, `permissoes`) VALUES
(1, 1, 'administrador', 0, 31),
(2, 16, 'coordenador', 0, 0),
(3, 17, 'coordenador', 0, 0),
(4, 18, 'coordenador', 0, 0),
(5, 19, 'professor', 1, 0),
(6, 20, 'professor', 0, 0),
(7, 21, 'professor', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Aluno`
--

CREATE TABLE IF NOT EXISTS `Aluno` (
  `numeroInscricao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Numero de inscricao do aluno',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse aluno representa',
  `status` enum('preinscrito','inscrito','desistente','formado','inativo') NOT NULL COMMENT 'Status desse aluno (pré-inscrito, inscrito, etc)',
  `idIndicador` int(11) DEFAULT NULL COMMENT 'Numero de inscricao do aluno que indicou esse aluno, caso aplicavel',
  `telefone` text NOT NULL COMMENT 'Telefone do aluno',
  `endereco` varchar(200) NOT NULL COMMENT 'Endereco completo do aluno',
  `escolaridade` enum('fundamental incompleto','fundamental completo','médio incompleto','médio completo','superior incompleto','superior completo','mestrado','doutorado') NOT NULL COMMENT 'Nível de escolaridade do aluno',
  `curso` varchar(200) DEFAULT NULL COMMENT 'Curso que o aluno frequentou, caso esteja no nível superior ou acima',
  `cep` varchar(8) NOT NULL COMMENT 'Código Postal do Aluno',
  `rua` varchar(255) NOT NULL COMMENT 'Rua do Aluno',
  `numero` int(11) NOT NULL COMMENT 'Numero do endereço do Aluno',
  `bairro` varchar(255) NOT NULL COMMENT 'Bairro do Aluno',
  `complemento` varchar(255) DEFAULT NULL COMMENT 'Complemento do Endereço',
  `estado` varchar(2) NOT NULL COMMENT 'Bairro em que o aluno reside',
  `cidade` varchar(255) NOT NULL COMMENT 'Cidade em que o aluno reside',
  `pais` varchar(3) NOT NULL COMMENT 'País que o aluno reside',
  PRIMARY KEY (`numeroInscricao`),
  KEY `idIndicador` (`idIndicador`),
  KEY `idAluno` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aluno do curso' AUTO_INCREMENT=12 ;

--
-- Dumping data for table `Aluno`
--

INSERT INTO `Aluno` (`numeroInscricao`, `idUsuario`, `status`, `idIndicador`, `telefone`, `endereco`, `escolaridade`, `curso`, `cep`, `rua`, `numero`, `bairro`, `complemento`, `estado`, `cidade`, `pais`) VALUES
(1, 2, 'preinscrito', NULL, '1693018232', '', 'médio completo', NULL, '14890470', 'Rua João Merchiori', 963, 'Jaboticabal', '', 'SP', 'São Paulo', 'BRL'),
(2, 3, 'preinscrito', 1, '1961438378', '', 'superior completo', 'Ciências Contábeis', '13098603', 'Rua Argeu Pires Neto', 149, 'Santa Amélia', 'Apto 400', 'SP', 'Campinas', 'BRL'),
(3, 4, 'preinscrito', NULL, '8260134527', '', 'médio completo', NULL, '57600830', 'Rua Coronel Antônio Pantaleão', 563, 'Monteiro Lobato', 'Apto 501, Bloco B', 'AL', 'Palmeira dos Índios', 'BRL'),
(4, 5, 'preinscrito', NULL, '6135342360', '', 'fundamental incompleto', NULL, '70645120', 'Quadra SRES Quadra 10', 1567, 'Maria José', 'Bloco L', 'DF', 'Cruzeiro', 'BRL'),
(5, 6, 'preinscrito', 4, '8698463979', '', 'fundamental incompleto', NULL, '64082670', 'Rua Laira', 715, 'Santa Mônica', '', 'PI', 'Teresina', 'BRL'),
(6, 7, 'preinscrito', NULL, '2169357517', '', 'fundamental incompleto', NULL, '21735110', 'Rua Professor Carvalho e Melo', 1856, 'Ottawa', '', 'RJ', 'Rio de Janeiro', 'BRL'),
(7, 8, 'preinscrito', NULL, '1184439221', '', 'fundamental incompleto', NULL, '31314333', 'Avenida São Paulo', 909, 'Hortêncio', 'Bloco A, Apto. 289', 'SP', 'Piracicaba', 'BRL'),
(8, 9, 'preinscrito', NULL, '8498876543', '', 'doutorado', 'Astrofísica quântica', '45543398', 'Rua Madagascar', 883, 'Alabama', '', 'RN', 'Taboleiro Grande', 'BRL'),
(9, 10, 'preinscrito', NULL, '5787659485', '', 'fundamental incompleto', NULL, '67754390', 'Rua dos Japoneses', 394, 'Violeta', '', 'AP', 'Macapá', 'BRL'),
(10, 11, 'preinscrito', NULL, '2098764959', '', 'fundamental incompleto', NULL, '98983399', 'Rua Almenara', 874, 'Jorema', '', 'GO', 'Goiânia', 'BRL'),
(11, 12, 'preinscrito', NULL, '3498123232', '', 'fundamental incompleto', NULL, '88744596', 'Avenida Silveira', 111, 'Capanema', '', 'MG', 'Uberlândia', 'BRL');

-- --------------------------------------------------------

--
-- Table structure for table `Artigo`
--

CREATE TABLE IF NOT EXISTS `Artigo` (
  `idArtigo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico',
  `autor` varchar(100) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `conteudo` text NOT NULL,
  `dataPublic` datetime NOT NULL COMMENT 'Data de publicacao do artigo',
  `tipo` enum('artigo','noticia') NOT NULL COMMENT 'Determina se é um artigo ou notícia',
  PRIMARY KEY (`idArtigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Artigo ou noticia a ser mostrada no site' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Associado`
--

CREATE TABLE IF NOT EXISTS `Associado` (
  `idAssoc` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico do associado',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse associado representa',
  `instituicao` enum('atenemg','conahom') NOT NULL COMMENT 'Nome da instituicao associada',
  `formacaoTerapeutica` varchar(200) NOT NULL COMMENT 'Formação terapêutica desse associado',
  `telefone` text NOT NULL COMMENT 'Telefone do associado',
  `endereco` varchar(200) NOT NULL COMMENT 'Endereco completo do associado',
  `cidade` varchar(100) NOT NULL COMMENT 'Cidade de residência do associado',
  `estado` text NOT NULL COMMENT 'UF de residência do associado',
  `numObjeto` varchar(100) DEFAULT NULL COMMENT 'Código da carteirinha no correio',
  `dataEnvioCarteirinha` date DEFAULT NULL COMMENT 'Data de envio da carteirinha',
  `enviouDocumentos` tinyint(1) NOT NULL COMMENT 'Determina se o associado já enviou os documentos necessários e foi aprovado',
  `cep` varchar(8) NOT NULL COMMENT 'Código Postal do Associado',
  `rua` varchar(255) NOT NULL COMMENT 'Rua do associado',
  `numero` int(11) NOT NULL COMMENT 'Numero do endereço do Associado',
  `bairro` varchar(255) NOT NULL COMMENT 'Bairro do Associado',
  `complemento` varchar(255) DEFAULT NULL COMMENT 'Complemento do Endereço',
  `pais` varchar(3) NOT NULL COMMENT 'País que o Associado reside',
  PRIMARY KEY (`idAssoc`),
  KEY `idUsuario` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Associado da CONAHOM/ATENEMG' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Associado`
--

INSERT INTO `Associado` (`idAssoc`, `idUsuario`, `instituicao`, `formacaoTerapeutica`, `telefone`, `endereco`, `cidade`, `estado`, `numObjeto`, `dataEnvioCarteirinha`, `enviouDocumentos`, `cep`, `rua`, `numero`, `bairro`, `complemento`, `pais`) VALUES
(1, 13, 'conahom', 'Quiropraxia', '2487348942', '', 'Nova Lima', 'MG', NULL, NULL, 1, '43857654', 'Rua Nogueira', 98, 'Carijós', '', 'BRL'),
(2, 14, 'atenemg', 'Florais', '2398575677', '', 'Belém', 'PA', NULL, NULL, 0, '30843030', 'Rua Lobo Soares', 87, 'Jordânia', '', 'BRL'),
(3, 15, 'conahom', 'Tratamentos de longo prazo', '3372383294', '', 'Palmas', 'TO', NULL, NULL, 0, '44556544', 'Rua das Flores', 83, 'Especial', '', 'BRL');

-- --------------------------------------------------------

--
-- Table structure for table `Aula`
--

CREATE TABLE IF NOT EXISTS `Aula` (
  `idAula` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de aula',
  `chaveCidade` int(11) NOT NULL COMMENT 'Identificador da cidade onde ocorrera essa aula',
  `etapa` int(11) NOT NULL COMMENT 'Numero da etapa a qual essa aula se refere',
  `data` datetime NOT NULL COMMENT 'Data e horario da aula',
  `idProfessor` int(11) DEFAULT NULL COMMENT 'Identificador único do professor que ministrara a aula',
  `nota` float DEFAULT NULL COMMENT 'Media das notas dadas a essa aula pelos alunos',
  `descricao` varchar(10000) NOT NULL COMMENT 'Descrição do conteúdo que será dado nessa aula',
  PRIMARY KEY (`idAula`),
  KEY `chaveCidade` (`chaveCidade`),
  KEY `idProfessor` (`idProfessor`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aula lancada no sistema' AUTO_INCREMENT=8 ;

--
-- Dumping data for table `Aula`
--

INSERT INTO `Aula` (`idAula`, `chaveCidade`, `etapa`, `data`, `idProfessor`, `nota`, `descricao`) VALUES
(1, 1, 1, '2014-03-03 12:30:00', 5, NULL, 'Introdução à Homeopatia'),
(2, 1, 2, '2014-04-03 12:30:00', 5, NULL, 'Continuando as bases da alopatia'),
(3, 2, 1, '2014-06-05 14:30:00', 7, NULL, 'Conceitos homeopáticos básicos'),
(4, 2, 1, '2014-09-03 08:30:00', 7, NULL, 'Conclusões da etapa'),
(5, 2, 1, '2014-12-10 09:00:00', 7, NULL, 'Guia de estudos para as férias'),
(6, 2, 3, '2014-03-03 08:00:00', 7, NULL, 'Continuação dos estudos sobre florais'),
(7, 2, 4, '2014-07-05 17:00:00', 7, NULL, 'Grandes homeopatas da história');

-- --------------------------------------------------------

--
-- Table structure for table `Cidade`
--

CREATE TABLE IF NOT EXISTS `Cidade` (
  `idCidade` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de cidade',
  `UF` text NOT NULL,
  `ano` int(10) unsigned NOT NULL COMMENT 'Ano para o qual essa oferta do curso vale',
  `nome` varchar(100) NOT NULL,
  `idCoordenador` int(11) NOT NULL COMMENT 'Identificador unico do coordenador dessa cidade',
  `local` varchar(200) NOT NULL,
  `precoInscricao` float NOT NULL COMMENT 'Valor a ser pago para a inscrição nessa cidade',
  `precoParcela` float NOT NULL COMMENT 'Valor da parcela a ser pago mensalmente por alunos dessa cidade',
  `limiteInscricao` date NOT NULL COMMENT 'Data limite para matrícula nessa cidade',
  `nomeEmpresa` varchar(100) NOT NULL COMMENT 'Nome da empresa responsável por essa cidade',
  `cnpjEmpresa` char(14) NOT NULL COMMENT 'CNPJ da empresa responsável por essa cidade',
  PRIMARY KEY (`idCidade`),
  KEY `idCoordenador` (`idCoordenador`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Oferta de curso em determinada cidade em determinado período' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Cidade`
--

INSERT INTO `Cidade` (`idCidade`, `UF`, `ano`, `nome`, `idCoordenador`, `local`, `precoInscricao`, `precoParcela`, `limiteInscricao`, `nomeEmpresa`, `cnpjEmpresa`) VALUES
(1, 'MG', 2014, 'Belo Horizonte', 3, 'Faculdade de odontologia da UFMG', 100, 30, '2014-05-02', 'Homeobrás', '56667868000102'),
(2, 'RJ', 2014, 'Rio de Janeiro', 2, 'Faculdade de odontologia da UFRJ', 90, 85, '2014-05-02', 'Homeobrás', '56667868000102'),
(3, 'SP', 2014, 'São Paulo', 4, 'Faculdade de odontologia da USP', 120, 80, '2014-08-10', 'Homeobrás', '56667868000102');

-- --------------------------------------------------------

--
-- Table structure for table `Compra`
--

CREATE TABLE IF NOT EXISTS `Compra` (
  `idCompra` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de compra',
  `cpf` char(11) NOT NULL COMMENT 'CPF do comprador',
  `nome` varchar(100) NOT NULL,
  `data` date NOT NULL COMMENT 'Data da compra',
  `contato` varchar(15) NOT NULL,
  PRIMARY KEY (`idCompra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Compra de produtos' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Evento`
--

CREATE TABLE IF NOT EXISTS `Evento` (
  `idEvento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico',
  `dataPublic` datetime NOT NULL COMMENT 'Data de publicacao dos dados do evento no site',
  `dataEvento` datetime NOT NULL COMMENT 'Data em que ocorrera o evento',
  `titulo` varchar(100) NOT NULL,
  `local` varchar(500) NOT NULL,
  `descricao` varchar(3000) NOT NULL,
  PRIMARY KEY (`idEvento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dados de um evento a serem mostrados no site' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Frequencia`
--

CREATE TABLE IF NOT EXISTS `Frequencia` (
  `chaveAluno` int(11) NOT NULL DEFAULT '0' COMMENT 'Numero de inscricao do aluno ao qual essa frequencia se relaciona',
  `chaveAula` int(11) NOT NULL COMMENT 'Identificador da aula a qual essa frequencia se refere',
  `presenca` tinyint(1) NOT NULL COMMENT 'Representa se o aluno estava presente nessa aula ou nao',
  `jaAvaliou` tinyint(1) NOT NULL COMMENT 'Determina se o aluno já avaliou essa aula ou não',
  PRIMARY KEY (`chaveAula`,`chaveAluno`),
  KEY `chaveAluno` (`chaveAluno`),
  KEY `chaveAula` (`chaveAula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lancamento da presenca ou ausencia de um aluno em uma aula';

-- --------------------------------------------------------

--
-- Table structure for table `Livro`
--

CREATE TABLE IF NOT EXISTS `Livro` (
  `idLivro` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de livro',
  `valor` float NOT NULL COMMENT 'Preco do livro',
  `quantidade` int(10) unsigned NOT NULL COMMENT 'Quantidade do livro em estoque',
  `nome` varchar(500) NOT NULL,
  `autor` varchar(100) NOT NULL,
  `editora` varchar(100) NOT NULL,
  `dataPublic` date NOT NULL COMMENT 'Data da publicacao do livro',
  `edicao` int(10) unsigned NOT NULL COMMENT 'Numero da edicao do livro',
  `fornecedor` varchar(200) NOT NULL,
  PRIMARY KEY (`idLivro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Livros a venda no sistema' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Matricula`
--

CREATE TABLE IF NOT EXISTS `Matricula` (
  `idMatricula` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da matrícula feita por um aluno',
  `chaveAluno` int(11) NOT NULL COMMENT 'Identificador do aluno ao qual essa matrícula se refere',
  `etapa` int(10) unsigned NOT NULL COMMENT 'Etapa a qual essa matrícula se refere',
  `aprovado` tinyint(1) DEFAULT NULL COMMENT 'Determina se o aluno (já) foi aprovado ou não',
  `chaveCidade` int(11) NOT NULL COMMENT 'Identificador da cidade a qual essa matrícula se refere',
  PRIMARY KEY (`idMatricula`),
  KEY `chaveAluno` (`chaveAluno`),
  KEY `chaveCidade` (`chaveCidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Matrícula de um aluno em uma etapa em determinado período' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Notificacao`
--

CREATE TABLE IF NOT EXISTS `Notificacao` (
  `idNotificacao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único dessa notificação',
  `titulo` varchar(100) NOT NULL COMMENT 'Título da notificação a ser dada ao aluno',
  `texto` varchar(500) NOT NULL COMMENT 'Texto da notificação a ser dada ao aluno',
  `chaveAluno` int(11) NOT NULL COMMENT 'Número de matrícula do aluno para o qual deve ser mostrada a notificação',
  `lida` tinyint(1) NOT NULL COMMENT 'Determina se a notificação já foi lida ou não',
  PRIMARY KEY (`idNotificacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Representa uma notificação a ser mostrada para o aluno na página principal' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Pagseguro`
--

CREATE TABLE IF NOT EXISTS `Pagseguro` (
  `placeholder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dados referentes ao sistema Pagseguro (a modelar)';

-- --------------------------------------------------------

--
-- Table structure for table `Pedido`
--

CREATE TABLE IF NOT EXISTS `Pedido` (
  `chaveProduto` int(11) NOT NULL COMMENT 'Identificador unico do produto sendo comprado',
  `chaveCompra` int(11) NOT NULL COMMENT 'Identificador unico da compra a qual esse pedido pertence',
  `quantidade` int(10) unsigned NOT NULL COMMENT 'Quantidade desse produto que sera comprada',
  PRIMARY KEY (`chaveCompra`,`chaveProduto`),
  KEY `livroComprado` (`chaveProduto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Compra de um produto especifico,referenciado por uma compra de mais produtos';

-- --------------------------------------------------------

--
-- Table structure for table `PgtoAnuidade`
--

CREATE TABLE IF NOT EXISTS `PgtoAnuidade` (
  `idPagAnuidade` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de pagamento de anuidade',
  `chaveAssoc` int(11) NOT NULL COMMENT 'Identificador do associado ao qual esse pagamento se refere',
  `inscricao` tinyint(1) NOT NULL COMMENT 'Determina se esse pagamento se refere a uma inscricao ou a uma anuidade',
  `valorTotal` float NOT NULL COMMENT 'Valor total a ser pago nessa anuidade/inscrição',
  `valorPago` float NOT NULL COMMENT 'Valor pago pelo associado',
  `metodo` varchar(100) NOT NULL,
  `data` datetime DEFAULT NULL COMMENT 'Data do pagamento da anuidade',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse pagamento se refere (pode ser diferente do ano especificado na data)',
  `fechado` tinyint(1) NOT NULL COMMENT 'Determina se o pagamento integral já foi feito ou não',
  PRIMARY KEY (`idPagAnuidade`),
  KEY `chaveAssoc` (`chaveAssoc`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Pagamento da anuidade de um associado' AUTO_INCREMENT=5;

-- --------------------------------------------------------

--
-- Table structure for table `PgtoCompra`
--

CREATE TABLE IF NOT EXISTS `PgtoCompra` (
  `idPagCompra` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de pagamento de uma compra',
  `cpf` int(11) NOT NULL COMMENT 'CPF do comprador do produto',
  `valor` float NOT NULL COMMENT 'Valor pago na compra',
  `chaveCompra` int(11) NOT NULL COMMENT 'Identificador unico da compra feita, ao qual esse pagamento se refere',
  `metodo` varchar(200) NOT NULL,
  `data` datetime NOT NULL COMMENT 'Data do pagamento',
  PRIMARY KEY (`idPagCompra`),
  KEY `chaveCompra` (`chaveCompra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Pagamento de algum produto' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `PgtoMensalidade`
--

CREATE TABLE IF NOT EXISTS `PgtoMensalidade` (
  `idPagMensalidade` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador do pagamento de mensalidade',
  `chaveMatricula` int(11) DEFAULT NULL COMMENT 'Numero da matrícula à qual esse pagamento se refere',
  `numParcela` int(11) NOT NULL COMMENT 'Numero da parcela ao qual esse pagamento se refere (deve ser 0 caso esse pagamento seja de inscrição)',
  `valorTotal` float NOT NULL COMMENT 'Valor total a ser pago nessa mensalidade',
  `valorPago` float NOT NULL COMMENT 'Valor pago pelo aluno',
  `desconto` float NOT NULL COMMENT 'Desconto (em %) recebido pelo aluno devido às indicações',
  `metodo` varchar(100) NOT NULL,
  `data` datetime DEFAULT NULL COMMENT 'Data na qual essa mensalidade foi paga',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse pagamento se refere (pode ser diferente do ano especificado na data)',
  `fechado` tinyint(1) NOT NULL COMMENT 'Determina se o pagamento integral já foi feito ou não',
  PRIMARY KEY (`idPagMensalidade`),
  KEY `chaveAluno` (`chaveMatricula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Pagamento de mensalidade ou inscricao de aluno' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Reuniao`
--

CREATE TABLE IF NOT EXISTS `Reuniao` (
  `idReuniao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico',
  `tema` varchar(200) NOT NULL,
  `data` datetime NOT NULL COMMENT 'Data em que ocorrera a reuniao',
  `descricao` varchar(3000) NOT NULL,
  `local` varchar(500) NOT NULL,
  PRIMARY KEY (`idReuniao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Dados de reunião a serem mostrados no site' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `Trabalho`
--

CREATE TABLE IF NOT EXISTS `Trabalho` (
  `idTrabalho` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico do trabalho',
  `chaveAluno` int(11) NOT NULL COMMENT 'Numero de inscricao do aluno que fez esse trabalho',
  `dataEntrega` datetime NOT NULL COMMENT 'Data e hora em que esse trabalho foi entregue',
  `chaveDefinicao` int(11) NOT NULL COMMENT 'Identificador unico da especificacao do trabalho enviado',
  `nota` int(10) unsigned DEFAULT NULL COMMENT 'Nota do trabalho',
  `comentarioProfessor` varchar(5000) DEFAULT NULL COMMENT 'Comentário do professor sobre o trabalho do aluno',
  `extensao` char(10) NOT NULL COMMENT 'Tipo de arquivo enviado (pdf, doc, ppt, etc)',
  PRIMARY KEY (`idTrabalho`),
  KEY `chaveAluno` (`chaveAluno`),
  KEY `chaveDefinicao` (`chaveDefinicao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Trabalho enviado por aluno' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `TrabalhoDefinicao`
--

CREATE TABLE IF NOT EXISTS `TrabalhoDefinicao` (
  `idDefTrabalho` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico de definicao de trabalho',
  `titulo` varchar(300) NOT NULL COMMENT 'Titulo do trabalho',
  `etapa` int(10) unsigned NOT NULL COMMENT 'Etapa do curso a qual se refere esse trabalho',
  `descricao` varchar(10000) NOT NULL,
  `dataLimite` datetime NOT NULL COMMENT 'Data e hora limite de entrega do trabalho',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse trabalho se refere',
  PRIMARY KEY (`idDefTrabalho`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Especificacao dada para a confeccao de um trabalho por parte dos alunos' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `TrabalhoDefinicao`
--

INSERT INTO `TrabalhoDefinicao` (`idDefTrabalho`, `titulo`, `etapa`, `descricao`, `dataLimite`, `ano`) VALUES
(1, 'Introdução à Homeopatia', 1, 'Trabalho introdutório para as turmas:\r\n\r\nVocê deve fazer um trabalho que [...]', '2014-06-09 00:00:00', 2014),
(2, 'Revisão do curso', 4, 'Esse trabalho deve resumir tudo o que você já aprendeu.\r\n\r\nVocê deve [...]', '2014-12-10 00:00:00', 2014),
(3, 'Problemas de homeopatia', 3, 'Você deve listar todos os problemas no cenário da Homeopatia atual, fazendo [...]', '2015-09-09 00:00:00', 2014),
(4, 'Trabalho orientado', 4, 'Com a ajuda do seu orientador, [...]', '2014-06-10 00:00:00', 2014);

-- --------------------------------------------------------

--
-- Table structure for table `Usuario`
--

CREATE TABLE IF NOT EXISTS `Usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de usuário',
  `cpf` char(11) NOT NULL COMMENT 'Cadastro de Pessoa Fisica do usuario',
  `dataInscricao` datetime NOT NULL COMMENT 'Data de inscricao no sistema',
  `email` varchar(100) NOT NULL,
  `login` varchar(100) NOT NULL,
  `senha` text NOT NULL,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `cpf` (`cpf`),
  KEY `cpf_2` (`cpf`),
  KEY `dataInscricao` (`dataInscricao`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Usuario do sistema, que pode ser aluno, associado ou administrador' AUTO_INCREMENT=22 ;

--
-- Dumping data for table `Usuario`
--

INSERT INTO `Usuario` (`id`, `cpf`, `dataInscricao`, `email`, `login`, `senha`, `nome`) VALUES
(1, '11989183654', '2014-07-14 11:31:56', 'luc.aug.freire@gmail.com', 'admin', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Lucas'),
(2, '81763492168', '2014-11-12 10:12:49', 'victorcastrocarvalho@armyspy.com', 'victor1994', '$2a$08$RpGAR3lVb3RdDDmRALqYCOFaRsfm7GP7PcdWu233ZGdNW2E6J5Q5e', 'Victor Castro Carvalho'),
(3, '64705249070', '2014-11-12 10:15:41', 'viniciusalvessilva@teleworm.us', 'vinicius', '$2a$08$hJj9/hBGZfnrblpx.muqC.zlhru0j./kvn/9Hdz2HTQlsGUHlmJ26', 'Vinicius Alves Silva'),
(4, '83834893315', '2014-11-12 10:21:24', 'marianaferreirapinto@armyspy.com', 'mariana1234', '$2a$08$OsTyrZYsCSvMd4evTtFYA.O3UTY95oL05Y4t6M0JHYvlmbJ9m9NVC', 'Mariana Ferreira Pinto'),
(5, '44236727315', '2014-11-12 10:24:16', 'annalimabarbosa@dayrep.com', 'annalima', '$2a$08$bwSrAdafNIsPRIkONKjWR.eZhRUioqwuGzHDFjM4jwxrIUKVtTtyi', 'Anna Lima Barbosa'),
(6, '37128813128', '2014-11-12 10:29:07', 'feliperodriguesaraujo@rhyta.com', 'felipearaujo', '$2a$08$7iem7gWUy3uHYSXBrPoT5u3kEFIKEEqgBphK9fVeNLYGMZSVxRzb6', 'Felipe Rodrigues Araujo'),
(7, '39098656749', '2014-11-12 10:33:22', 'miguelsilvamartins@rhyta.com', 'miguelaluno', '$2a$08$otpV.e3xH0R6Sr1R3KOncOBZuNEoq24BvVVda.j07vQOp362WPaBK', 'Miguel Silva Martins'),
(8, '51841633011', '2014-11-12 10:36:05', 'antonio92@gmail.com', 'antonsil', '$2a$08$.BdNbxh8/A4Dljgh0ArZ6e3gtl0YtC5H8BTRGco09S/Ug9Z2V711q', 'Antônio José Silva'),
(9, '66242073455', '2014-11-12 10:40:10', 'amanda_ana@gmail.com', 'amanda', '$2a$08$AGDpYen1A3lD1KfMf5DZwubsXYZErmwnswGEApKL2eZaI/68ZGghq', 'Amanda Joana Pereira'),
(10, '02035862981', '2014-11-12 10:42:33', 'antonio_covers@gmail.com', 'getulio', '$2a$08$tcw/wvRLdYh/BJa7wFR1SO4vGwcat56OpHBYnSSCJJBIAiRkreQ7O', 'Getúlio Soares Albuquerque'),
(11, '23146265168', '2014-11-12 10:59:35', 'alfredo22@gmail.com', 'wellin', '$2a$08$rFHwv7LDO6tAN/HVEsqoS.JlBLceSNewBidtjaeMNeiX3kz1iUDUq', 'Wellington Alfredo Dias'),
(12, '61055784748', '2014-11-12 11:00:44', 'herc@gmail.com', 'hercules', '$2a$08$UIKre.q/kHjOQrTAXQjB3.yYVVFW9l.y6BxiUlVbDyxzsk6p//5qK', 'Hernando Hércules Ferreira'),
(13, '85479936492', '2014-11-12 11:06:36', 'joaojoao@gmail.com', 'joaocarlos', '$2a$08$4L7KmSz2RitcwdW9lkp48uI7uZM3a525FF7qotDhzVY7LEn4651rG', 'João Carlos Alberto'),
(14, '77721252245', '2014-11-12 11:08:20', 'ame.joana@gmail.com', 'amelia_flor', '$2a$08$HcZfTXY/8Kdylekb/9dSYO9HE8JIn8pu31bQKDB7DhfQ8n6YXF5Sa', 'Amélia Joana Glória'),
(15, '77721252245', '2014-11-12 11:10:44', 'juju.silva@gmail.com', 'juju', '$2a$08$aCColQUnDPf.jOP93CKueuSeoSqDSIA82uI5dcqqUoaBI1gAtLgam', 'Júlia Silva'),
(16, '17125261540', '2014-11-12 11:12:12', 'fernando.filho@gmail.com', 'nando', '$2a$08$axsrInHv6.bBo8J0Ef7qxOAASLAoH4FM2kVnXDmDZApZ5aV1Aes72', 'Fernando Faria Filho'),
(17, '68322372787', '2014-11-12 11:13:36', 'cassio.murilo@gmail.com', 'camum', '$2a$08$JjcHZKPEMGXqcm2m4R5dsexqGE56BEFI0nj4oyMk6zvWFFTPdkncW', 'Cássio Murilo de Oliveira'),
(18, '77702570776', '2014-11-12 11:14:14', 'jess1231@gmail.com', 'jessica', '$2a$08$dGC9vIFIC4ayGzwx3v16j.Gm9f.cYSfaf9Y.KqiEcA17J1lMvYj86', 'Jéssica Martins Pereira'),
(19, '22783139758', '2014-11-12 11:15:14', 'kaioherb@gmail.com', 'kaio_h', '$2a$08$Re5inJAd5RUtsnwDVhRT.uR4uTeric.L2MdPPVpnz7jFmzYCgxnhS', 'Kaio Herberto Lobo'),
(20, '83872687808', '2014-11-12 11:17:29', 'xavier_x@gmail.com', 'xavier', '$2a$08$LmF6XVIzbiMkWblg9lngdesJqdMq8g33Jyrlgh9MXznFwyTtU856y', 'Xavier Souza Ferreira'),
(21, '64391809834', '2014-11-12 11:18:34', 'monica@gmail.com', 'monica', '$2a$08$sKIsk.1PtkNISMKhi1iw/eLVuXFuE6Omp8mz8/6x1G6953vSh/O86', 'Mônica Horta Freire');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Administrador`
--
ALTER TABLE `Administrador`
  ADD CONSTRAINT `UsuarioAdmin` FOREIGN KEY (`idUsuario`) REFERENCES `Usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Aluno`
--
ALTER TABLE `Aluno`
  ADD CONSTRAINT `AlunoIndicador` FOREIGN KEY (`idIndicador`) REFERENCES `Aluno` (`numeroInscricao`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `UsuarioAluno` FOREIGN KEY (`idUsuario`) REFERENCES `Usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Associado`
--
ALTER TABLE `Associado`
  ADD CONSTRAINT `UsuarioAssoc` FOREIGN KEY (`idUsuario`) REFERENCES `Usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Aula`
--
ALTER TABLE `Aula`
  ADD CONSTRAINT `cidadeAula` FOREIGN KEY (`chaveCidade`) REFERENCES `Cidade` (`idCidade`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `ProfessorAula` FOREIGN KEY (`idProfessor`) REFERENCES `Administrador` (`idAdmin`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Cidade`
--
ALTER TABLE `Cidade`
  ADD CONSTRAINT `coordenadorCidade` FOREIGN KEY (`idCoordenador`) REFERENCES `Administrador` (`idAdmin`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Frequencia`
--
ALTER TABLE `Frequencia`
  ADD CONSTRAINT `aulaFreq` FOREIGN KEY (`chaveAula`) REFERENCES `Aula` (`idAula`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `freqAluno` FOREIGN KEY (`chaveAluno`) REFERENCES `Aluno` (`numeroInscricao`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Matricula`
--
ALTER TABLE `Matricula`
  ADD CONSTRAINT `alunoMatricula` FOREIGN KEY (`chaveAluno`) REFERENCES `Aluno` (`numeroInscricao`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cidadeMatriculado` FOREIGN KEY (`chaveCidade`) REFERENCES `Cidade` (`idCidade`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Pedido`
--
ALTER TABLE `Pedido`
  ADD CONSTRAINT `compraPedido` FOREIGN KEY (`chaveCompra`) REFERENCES `Compra` (`idCompra`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `livroComprado` FOREIGN KEY (`chaveProduto`) REFERENCES `Livro` (`idLivro`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `PgtoAnuidade`
--
ALTER TABLE `PgtoAnuidade`
  ADD CONSTRAINT `AssocPagamento` FOREIGN KEY (`chaveAssoc`) REFERENCES `Associado` (`idAssoc`) ON UPDATE CASCADE;

--
-- Constraints for table `PgtoCompra`
--
ALTER TABLE `PgtoCompra`
  ADD CONSTRAINT `compraPgto` FOREIGN KEY (`chaveCompra`) REFERENCES `Compra` (`idCompra`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `PgtoMensalidade`
--
ALTER TABLE `PgtoMensalidade`
  ADD CONSTRAINT `MatriculaPgto` FOREIGN KEY (`chaveMatricula`) REFERENCES `Matricula` (`idMatricula`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Trabalho`
--
ALTER TABLE `Trabalho`
  ADD CONSTRAINT `alunoTrabalho` FOREIGN KEY (`chaveAluno`) REFERENCES `Aluno` (`numeroInscricao`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `definicaoTrab` FOREIGN KEY (`chaveDefinicao`) REFERENCES `TrabalhoDefinicao` (`idDefTrabalho`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
