-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 17, 2014 at 01:42 PM
-- Server version: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

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
  PRIMARY KEY (`idAdmin`),
  KEY `idUsuario` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Administradores do sistema' AUTO_INCREMENT=18 ;

--
-- Dumping data for table `Administrador`
--

INSERT INTO `Administrador` (`idAdmin`, `idUsuario`, `nivel`, `corrigeTrabalho`) VALUES
(1, 1, 'administrador', 0),
(3, 3, 'professor', 0),
(4, 4, 'coordenador', 0),
(7, 9, 'administrador', 0),
(8, 11, 'administrador', 0),
(10, 27, 'administrador', 0),
(11, 30, 'coordenador', 0),
(12, 31, 'professor', 1),
(13, 32, 'administrador', 0),
(14, 40, 'professor', 1),
(15, 41, 'professor', 1),
(16, 42, 'professor', 0),
(17, 48, 'administrador', 0);

-- --------------------------------------------------------

--
-- Table structure for table `Aluno`
--

CREATE TABLE IF NOT EXISTS `Aluno` (
  `numeroInscricao` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Numero de inscricao do aluno',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse aluno representa',
  `status` enum('preinscrito','inscrito','desistente','formado') NOT NULL COMMENT 'Status desse aluno (pré-inscrito, inscrito, etc)',
  `idIndicador` int(11) DEFAULT NULL COMMENT 'Numero de inscricao do aluno que indicou esse aluno, caso aplicavel',
  `telefone` text NOT NULL COMMENT 'Telefone do aluno',
  `endereco` varchar(200) NOT NULL COMMENT 'Endereco completo do aluno',
  `escolaridade` enum('fundamental incompleto','fundamental completo','médio incompleto','médio completo','superior incompleto','superior completo','mestrado','doutorado') NOT NULL COMMENT 'Nível de escolaridade do aluno',
  `curso` varchar(200) DEFAULT NULL COMMENT 'Curso que o aluno frequentou, caso esteja no nível superior ou acima',
  PRIMARY KEY (`numeroInscricao`),
  KEY `idIndicador` (`idIndicador`),
  KEY `idAluno` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aluno do curso' AUTO_INCREMENT=27 ;

--
-- Dumping data for table `Aluno`
--

INSERT INTO `Aluno` (`numeroInscricao`, `idUsuario`, `status`, `idIndicador`, `telefone`, `endereco`, `escolaridade`, `curso`) VALUES
(3, 12, 'inscrito', NULL, '2147483647', 'Avenida B, 303, Apto 400, Belo Horizonte, Minas Gerais', 'doutorado', 'Aquacultura'),
(4, 13, 'inscrito', NULL, '2147483647', 'Rua A Cidade B', 'médio completo', NULL),
(6, 15, 'inscrito', NULL, '319988776611', 'Avenida bairro cidade estado', 'superior incompleto', 'Ciência da Computação'),
(7, 17, 'inscrito', NULL, '1212123434', 'Minha casa', 'superior completo', 'Ciência da Computação'),
(9, 19, 'inscrito', NULL, '3166554433', 'サボテン', 'fundamental incompleto', NULL),
(10, 20, 'inscrito', NULL, '3144556677', 'asdfsjhgffds', 'fundamental incompleto', NULL),
(11, 23, 'inscrito', 4, '2121212121', 'saddsasdsdadsaasdadssda', 'fundamental incompleto', NULL),
(12, 24, 'inscrito', NULL, '9999999999', 'sadkfçlfjçlk', 'fundamental incompleto', NULL),
(13, 25, 'inscrito', NULL, '2222222222', 'テストテストテストテスト', 'fundamental incompleto', NULL),
(14, 29, 'inscrito', NULL, '3199887766', 'aaaaaaaaaaaaaaaa', 'fundamental incompleto', NULL),
(15, 33, 'inscrito', NULL, '3189987777', 'saddsaadsdsa', 'fundamental incompleto', NULL),
(16, 34, 'inscrito', NULL, '3199887766', 'teste ende', 'fundamental incompleto', NULL),
(18, 36, 'preinscrito', NULL, '3199887766', 'asdfasdf', 'fundamental incompleto', NULL),
(19, 37, 'inscrito', 4, '3199999999', 'Mão', 'fundamental incompleto', NULL),
(20, 38, 'preinscrito', NULL, '3112345432', 'asdfasdf', 'fundamental incompleto', NULL),
(21, 39, 'preinscrito', NULL, '3199887766', 'Rua da rua da', 'fundamental incompleto', NULL),
(22, 47, 'inscrito', 14, '3199887766', 'asdfasdf', 'fundamental incompleto', NULL),
(23, 49, 'preinscrito', NULL, '3199887766', 'itamanai yo', 'fundamental incompleto', NULL),
(24, 50, 'preinscrito', NULL, '3199887766', 'Saiba que o problema é seu (I pray for children down in Aaaaaaaaaaafricaaaaaaaaaaaa)', 'fundamental incompleto', NULL),
(25, 51, 'preinscrito', NULL, '3188776655', 'Haaa dsadkjaskda', 'fundamental incompleto', NULL),
(26, 53, 'preinscrito', NULL, '3199887766', 'asdfasdf', 'fundamental incompleto', NULL);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Artigo ou noticia a ser mostrada no site' AUTO_INCREMENT=21 ;

--
-- Dumping data for table `Artigo`
--

INSERT INTO `Artigo` (`idArtigo`, `autor`, `titulo`, `conteudo`, `dataPublic`, `tipo`) VALUES
(1, 'Autor', 'Extra extra', 'Autor faz notícia', '2014-07-16 11:09:21', 'noticia'),
(2, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:15:34', 'noticia'),
(3, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:15:45', 'noticia'),
(4, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:11', 'noticia'),
(5, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:13', 'noticia'),
(6, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:14', 'noticia'),
(7, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:14', 'noticia'),
(8, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:15', 'noticia'),
(9, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:15', 'noticia'),
(10, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:16', 'noticia'),
(11, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:16', 'noticia'),
(12, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:16', 'noticia'),
(13, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:17', 'noticia'),
(14, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:17', 'noticia'),
(15, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:17', 'noticia'),
(17, 'Aute', 'Teste', 'Extra extre extro etiikes', '2014-07-22 14:17:18', 'noticia'),
(18, 'ルカス', '日本語', 'すごいね！ このシステをム日本語の言葉', '2014-07-29 09:38:39', 'noticia'),
(19, 'Teste', 'Teste', 'Apenas testando', '2014-08-01 09:02:19', 'artigo'),
(20, 'Datarock', 'Computer Camp Love', 'I ran into her on computer camp', '2014-09-04 16:12:58', 'artigo');

-- --------------------------------------------------------

--
-- Table structure for table `Associado`
--

CREATE TABLE IF NOT EXISTS `Associado` (
  `idAssoc` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador unico do associado',
  `idUsuario` int(11) NOT NULL COMMENT 'Identificador único do usuário que esse associado representa',
  `instituicao` enum('atenemg','conahom') NOT NULL COMMENT 'Nome da instituicao associada',
  `telefone` text NOT NULL COMMENT 'Telefone do associado',
  `endereco` varchar(200) NOT NULL COMMENT 'Endereco completo do associado',
  `cidade` varchar(100) NOT NULL COMMENT 'Cidade de residência do associado',
  `estado` text NOT NULL COMMENT 'UF de residência do associado',
  `enviouDocumentos` tinyint(1) NOT NULL COMMENT 'Determina se o associado já enviou os documentos necessários e foi aprovado',
  PRIMARY KEY (`idAssoc`),
  KEY `idUsuario` (`idUsuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Associado da CONAHOM/ATENEMG' AUTO_INCREMENT=9 ;

--
-- Dumping data for table `Associado`
--

INSERT INTO `Associado` (`idAssoc`, `idUsuario`, `instituicao`, `telefone`, `endereco`, `cidade`, `estado`, `enviouDocumentos`) VALUES
(2, 28, 'atenemg', '3199887766', 'dsasdadsa', '', '', 1),
(3, 43, 'conahom', '3166600666', 'Maltat', '', '', 1),
(5, 45, 'atenemg', '3199886655', 'Aperture Science, 200. Perto da Black Mesa.', '', '', 1),
(6, 46, 'conahom', '3144444444', 'I SEE PEOPLE ON THE FLOOR', '', '', 0),
(7, 52, 'atenemg', '3199884433', 'Rua da rua da', 'Hyrule', 'RO', 1),
(8, 54, 'atenemg', '3133221122', 'Enredeçdas', 'Belo HoriroH', 'ES', 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aula lancada no sistema' AUTO_INCREMENT=25 ;

--
-- Dumping data for table `Aula`
--

INSERT INTO `Aula` (`idAula`, `chaveCidade`, `etapa`, `data`, `idProfessor`, `nota`, `descricao`) VALUES
(10, 4, 1, '2014-02-01 12:30:00', 3, 50, ''),
(11, 4, 3, '2014-03-03 14:40:00', 3, NULL, ''),
(12, 4, 2, '2014-06-06 13:30:00', 3, 50, ''),
(13, 9, 1, '2014-04-08 19:00:00', 3, 50, ''),
(14, 9, 3, '2014-06-06 18:30:00', 3, NULL, 'Isso mesmo, agora essa aula tem a exclusiva descrição (não vendida separadamente)'),
(15, 3, 3, '2014-03-08 08:30:00', 3, 53.3334, ''),
(16, 4, 1, '2014-03-08 15:30:00', 12, NULL, 'CALYPSOOOOOOOOO'),
(17, 4, 1, '2014-03-09 20:30:00', 3, NULL, ''),
(18, 13, 1, '2014-09-09 13:00:00', 12, NULL, 'Êêêê ôôô, vida de gado'),
(19, 4, 1, '2014-07-17 03:00:00', 14, NULL, ''),
(20, 2, 2, '2013-05-05 12:12:00', 3, 66.6667, ''),
(21, 4, 1, '2014-09-09 12:22:00', 14, NULL, ''),
(22, 4, 1, '2014-04-03 09:38:00', 14, NULL, ''),
(23, 4, 1, '2014-04-03 09:38:00', 14, NULL, 'Finge que sabe a matéria e ensina alguma coisa. Sei lá, finge que ensina PLA, PAL, complemento... *fica sem ar* de dois...\r\n\r\nEnsina que -32 = 32\r\n\r\nDá um TP sobre Verilog I WAS SOLID GOLD'),
(24, 18, 1, '2002-07-18 09:00:00', NULL, NULL, 'adfhsd');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Oferta de curso em determinada cidade em determinado período' AUTO_INCREMENT=20 ;

--
-- Dumping data for table `Cidade`
--

INSERT INTO `Cidade` (`idCidade`, `UF`, `ano`, `nome`, `idCoordenador`, `local`, `precoInscricao`, `precoParcela`, `limiteInscricao`, `nomeEmpresa`, `cnpjEmpresa`) VALUES
(1, 'ES', 2014, 'Vitória', 4, 'Faculdade homeopata', 12, 0, '2015-08-07', 'Homeobrás', '31206600000134'),
(2, 'MG', 2013, 'Belo Horizonte', 4, 'Faculdade de odontologia da UFMG', 0, 0, '2014-12-31', '', ''),
(3, 'RJ', 2014, 'Rio de Janeiro', 4, 'UFRJ', 560.8, 120.9, '2014-12-27', '', ''),
(4, 'PA', 2014, 'Belém', 4, 'Castanha', 6500, 150, '2014-07-31', 'fa fa fa fafafafa fa', '23231123243143'),
(9, 'AC', 2014, 'Laticínios acrelândia', 4, 'sadfg', 321, 123, '2014-05-05', '', ''),
(10, 'AP', 2014, 'Teste', 4, 'Abcd', 122, 21, '2028-01-03', '', ''),
(11, 'AC', 2003, 'Saboten', 4, 'Gurafichi', 123, 12, '2002-09-02', '', ''),
(12, 'AC', 2005, 'adsadsads', 4, 'dafsfdsfdgf', 122, 44, '2005-02-01', '', ''),
(13, 'BA', 2014, 'Cidadizinha', 4, 'ababa', 98, 89, '2014-10-09', '', ''),
(16, 'ES', 2014, 'Graceless', 4, 'Is there a powder to erase this', 99, 2, '2014-02-02', 'AH ÉEEEEA', '31206600000134'),
(17, 'AC', 2014, 'lfgdjk', 4, 'kdsladlkaads', 332, 2, '2014-03-02', 'fdfsdsfdfsd', '61929577000177'),
(18, 'MG', 2002, 'Belo Horizonte', 4, 'UFMG', 75, 15, '2014-07-17', 'asdf', '81535238000113'),
(19, 'AM', 2003, 'abacartea<br><br></select> <!--', 4, 'floresta', 67, 76, '2030-09-09', 'Homebros', '81535238000113');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Dados de um evento a serem mostrados no site' AUTO_INCREMENT=7 ;

--
-- Dumping data for table `Evento`
--

INSERT INTO `Evento` (`idEvento`, `dataPublic`, `dataEvento`, `titulo`, `local`, `descricao`) VALUES
(1, '2014-07-28 08:40:23', '2011-08-20 14:00:00', 'sdadsdsasad', 'abcdefé', 'adsadsadssdasadsadasdsdasad'),
(2, '2014-07-28 08:40:44', '2010-07-15 10:00:00', 'sadadsdsasdasda', 'abcdefe', 'dsadadsadssad'),
(3, '2014-07-28 08:42:58', '2010-07-15 10:00:00', 'sadadsdsasdasda', 'abcdefe', 'dsadadsadssad'),
(4, '2014-07-28 08:43:10', '2010-07-15 10:00:00', 'sadadsdsasdasda', 'abcdefe', 'dsadadsadssad'),
(5, '2014-07-28 08:44:47', '2010-07-30 21:58:00', 'dsasadads', 'abcdefé', 'dsasadsadsdasadads'),
(6, '2014-09-08 11:37:35', '2014-09-08 11:37:00', 'Teste não dasdasdasdsada', 'Aqui mesmo', 'Evento com nome que não tem a ver com dasdasdasdasdasd.');

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

--
-- Dumping data for table `Frequencia`
--

INSERT INTO `Frequencia` (`chaveAluno`, `chaveAula`, `presenca`, `jaAvaliou`) VALUES
(15, 10, 1, 1),
(18, 10, 1, 0),
(19, 10, 1, 1),
(4, 12, 1, 1),
(16, 12, 1, 1),
(9, 13, 1, 1),
(12, 13, 1, 1),
(13, 13, 1, 1),
(16, 13, 1, 1),
(3, 14, 1, 1),
(14, 14, 1, 1),
(3, 15, 1, 1),
(6, 15, 1, 1),
(9, 15, 1, 1),
(10, 15, 1, 1),
(11, 15, 1, 1),
(18, 15, 1, 0),
(15, 16, 1, 1),
(18, 16, 1, 0),
(19, 16, 1, 1),
(14, 20, 1, 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Livros a venda no sistema' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `Livro`
--

INSERT INTO `Livro` (`idLivro`, `valor`, `quantidade`, `nome`, `autor`, `editora`, `dataPublic`, `edicao`, `fornecedor`) VALUES
(1, 123, 1, 'O guia do guia', 'Thomas the tank engine', 'aaaaaaaaaaaa', '2016-05-16', 1, 'aaaaaaaaaaaaaaaaaaa');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Matrícula de um aluno em uma etapa em determinado período' AUTO_INCREMENT=82 ;

--
-- Dumping data for table `Matricula`
--

INSERT INTO `Matricula` (`idMatricula`, `chaveAluno`, `etapa`, `aprovado`, `chaveCidade`) VALUES
(64, 19, 1, NULL, 4),
(65, 18, 1, NULL, 4),
(66, 16, 2, NULL, 4),
(68, 14, 3, NULL, 9),
(69, 13, 1, NULL, 9),
(70, 12, 2, NULL, 9),
(71, 11, 1, NULL, 3),
(72, 10, 2, NULL, 3),
(73, 9, 3, NULL, 3),
(74, 7, 1, NULL, 3),
(75, 6, 3, NULL, 3),
(76, 4, 2, NULL, 4),
(77, 3, 3, NULL, 9),
(78, 21, 3, NULL, 10),
(79, 14, 2, 1, 2),
(80, 22, 3, NULL, 13),
(81, 15, 3, NULL, 4);

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
  `valor` float NOT NULL COMMENT 'Valor do pagamento',
  `metodo` varchar(100) NOT NULL,
  `data` datetime NOT NULL COMMENT 'Data do pagamento da anuidade',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse pagamento se refere (pode ser diferente do ano especificado na data)',
  PRIMARY KEY (`idPagAnuidade`),
  KEY `chaveAssoc` (`chaveAssoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Pagamento da anuidade de um associado' AUTO_INCREMENT=1 ;

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
  `numParcela` int(11) NOT NULL COMMENT 'Numero da parcela ao qual esse pagamento se refere',
  `valor` float NOT NULL COMMENT 'Valor pago nessa mensalidade',
  `metodo` varchar(100) NOT NULL,
  `data` datetime NOT NULL COMMENT 'Data na qual essa mensalidade foi paga',
  `ano` int(11) NOT NULL COMMENT 'Ano ao qual esse pagamento se refere (pode ser diferente do ano especificado na data)',
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Dados de reunião a serem mostrados no site' AUTO_INCREMENT=11 ;

--
-- Dumping data for table `Reuniao`
--

INSERT INTO `Reuniao` (`idReuniao`, `tema`, `data`, `descricao`, `local`) VALUES
(6, 'Reunião', '2013-08-15 00:00:00', 'Nessa reunião serão abordados temas homeopáticos d ehomeopatia homeopática homeopatosa. Para acompanhar o conteúdo é necessário ter vindo nas reuniões de 05/06/2007 e de 30/03/2014. Serão abordados vários temas, como o site novo que está quase pronto, apesar de não ser esse o objetivo da reunião, ora bolas.', 'Univerdidade univerdal do univerdo'),
(7, 'fdfasddsfa', '2014-08-03 08:36:23', 'fdadffdsa', 'fdsafdsadafsfsda'),
(10, 'Reunião dos atrasados', '2014-09-09 07:00:00', 'A reunião para pessoas que se atrasam demais. Na verdade a reunião começa às 19 horas, mas já sabemos que vocês vão atrasar, né.\r\n\r\nAnd I think I found it', 'UFMJ');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Trabalho enviado por aluno' AUTO_INCREMENT=11 ;

--
-- Dumping data for table `Trabalho`
--

INSERT INTO `Trabalho` (`idTrabalho`, `chaveAluno`, `dataEntrega`, `chaveDefinicao`, `nota`, `comentarioProfessor`, `extensao`) VALUES
(1, 14, '2014-08-28 16:46:11', 5, 76, '454345535545', 'txt'),
(4, 14, '2014-09-01 16:25:39', 4, 1, 'AHA UHUL O 1 É O NOVO 57', 'txt'),
(5, 14, '2014-09-04 16:15:28', 6, 42, 'oi guri ,seu trabái precisa de mais respostas (vide nota) :3', 'txt'),
(6, 14, '2014-09-10 11:26:16', 7, NULL, NULL, 'txt'),
(7, 16, '2014-09-10 11:26:48', 5, NULL, NULL, 'txt'),
(8, 22, '2014-09-10 11:31:36', 7, NULL, NULL, 'txt'),
(9, 15, '2014-09-10 16:20:05', 7, 0, 'MI RÉSPEITE VÉI SEINVERGONHA', 'txt'),
(10, 14, '2014-09-11 09:08:01', 9, NULL, NULL, 'doc');

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
  PRIMARY KEY (`idDefTrabalho`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Especificacao dada para a confeccao de um trabalho por parte dos alunos' AUTO_INCREMENT=10 ;

--
-- Dumping data for table `TrabalhoDefinicao`
--

INSERT INTO `TrabalhoDefinicao` (`idDefTrabalho`, `titulo`, `etapa`, `descricao`, `dataLimite`) VALUES
(4, 'Homeopatia prática escolástica', 2, 'Fazer uma apresentação do modelo aristotélico-platônico do teorema de Fermat. Mesmo que isso não faça sentido algum.', '2014-08-19 00:00:00'),
(5, 'Teste', 2, 'fdgjlklççl', '2014-09-02 00:00:00'),
(6, 'Trabalho atrasado', 3, 'Favor entregar esse trabalho atrasado. Porque aparentemente isso é legal', '2014-02-02 00:00:00'),
(7, 'Teste 2', 3, 'Tava aqui sem nada pra fazer, e decidi fazer pedir esse trabalho , vlw, flw.', '2014-11-27 00:00:00'),
(9, 'Teste agaom', 3, 'asdf', '2014-09-15 00:00:00');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Usuario do sistema, que pode ser aluno, associado ou administrador' AUTO_INCREMENT=55 ;

--
-- Dumping data for table `Usuario`
--

INSERT INTO `Usuario` (`id`, `cpf`, `dataInscricao`, `email`, `login`, `senha`, `nome`) VALUES
(1, '11989183654', '2014-07-14 11:31:56', 'luc.aug.freire@gmail.com', 'admin', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Lucas'),
(3, '11989183654', '2014-07-16 11:39:15', 'luc.aug.freire@gmail.com', 'éééé', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Sampops'),
(4, '11989183654', '2014-07-16 11:40:52', 'coordenador@homeopatias.com', 'coordenador', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Coordenador'),
(9, '11989183654', '2014-07-28 10:38:50', 'sdasadads@dsaads.dsa', 'adsadsad', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'kitsuné'),
(11, '11989183654', '2014-07-28 10:42:57', 'dasdsa@dsads.dsadda', 'DSAsdaadsasddd', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'éstados'),
(12, '20565525905', '2014-07-29 09:21:14', 'we@are.com', 'ender', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'endereço'),
(13, '62509436848', '2014-07-29 09:23:00', 'abcd@efg.hij', 'abcdef', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'abcdef'),
(15, '03627599799', '2014-07-29 09:50:45', 'teste@teste.com', 'esteste', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'aaaaaa'),
(17, '28446014211', '2014-07-29 16:59:58', 'teste@gmail.com', 'test', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Outro teste'),
(19, '59873949291', '2014-07-30 10:23:15', 'test@test.jp', 'サボテン', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'テスト'),
(20, '99999999999', '2014-07-31 10:34:17', 'fds@fds.fsd', 'asdfjhk', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Lucas Augustp Freore de Pçoveoira'),
(23, '34983523774', '2014-08-05 13:58:18', 'ednaldo@whatisthebrother.com', 'comcerteza', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Ednaldo Pereira'),
(24, '34983523774', '2014-08-05 13:59:10', 'kdaosasdok@dsok.sad', 'jkçlfda', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'okdsadakos'),
(25, '11989183654', '2014-08-05 14:48:19', 'aaaa@aaaaaaa.aaaa', 'テストテ', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'テスト'),
(27, '11989183654', '2014-08-05 15:11:00', 'aaa@aaa.aaa', 'logo existo', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'tento'),
(28, '99999999999', '2014-08-05 15:49:01', 'aaa@aaa.aa', 'associado', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'assoc'),
(29, '99999999999', '2014-08-05 15:54:03', 'aaaaaa@aaaaaaaaaaaa.aaaaaaaaaa', 'aluno', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'aluno'),
(30, '99999999999', '2014-08-05 15:58:13', 'tes@te.sa', 'coord', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Teste'),
(31, '99999999999', '2014-08-07 17:28:16', 'professor@gmail.com', 'professor', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Augusto Jorge'),
(32, '99999999999', '2014-08-11 12:50:28', 'testante@gmail.com', 'testando', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Admin testante'),
(33, '99999999999', '2014-08-13 14:19:53', 'dsa@sda.ads', 'aluno2', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'aluno2'),
(34, '99999999999', '2014-08-20 15:32:50', 'test@faslsad.ksda', 'alunonovo', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'aluno mudador louco'),
(36, '99999999999', '2014-09-01 11:13:08', 'asdf@fdsa.com', 'Aldo123', '$2a$08$gGbEstQTa2foGJ6/ZjLnU.4EC2q8Io0R9/kPAPRU5d1khH11J7VTq', 'Aldo'),
(37, '99999999999', '2014-09-01 15:16:07', 'indic@d.o', 'polegar', '$2a$08$bligUYrqSZedUO3/BivfEef7pqZgFjeJWl/MYwbIey51InczzqJSW', 'Indicado'),
(38, '99999999999', '2014-09-03 10:27:29', 'asdf@fdsa.com', 'jafuime', '$2a$08$QXBTa7tc3oJl./zUJI8iZ.8nh1jmifKSpTrP1hOuxGktPWOOmxXZi', 'ja fui'),
(39, '88888888888', '2014-09-04 15:54:02', 'gordon@blackmesa.com', 'gordodemesa', '$2a$08$E3vQOcTgnBNR2B1vwuyd1eYGD108we31flA0dBm2vXJeqfLiZmnfi', 'Mano gordo'),
(40, '99999999999', '2014-09-09 09:49:54', 'asdf@fdsa.com', 'nsam', '$2a$08$v5V4OJwqUa0/aUr5SQZ/aeuDvZRuicMELMEO91PUmMPznjS5CpjFi', 'Não Sei a Matéria'),
(41, '99999999999', '2014-09-09 15:05:18', 'ai@credo.com', 'quelinguaesquisita', '$2a$08$1/LLX/niIi.cyUZoUQuTxuBhrnNoXlysfzlibWLLRSCXQcE7nilm.', 'Professor ensinudo'),
(42, '99999999999', '2014-09-09 15:06:45', 'tenatosenta@gmail.com', 'tenato', '$2a$08$yW9zOpm0qe.tl8q5VwPfieHEmqjAj2OkOXA./yJH2S7tTNFEkSpTi', 'Senra'),
(43, '99999999999', '2014-09-09 16:34:49', 'malv@din.hihi', 'associaçãsôs', '$2a$08$7aXJZjeWjcdaqeg1tOZSZ.ukgDlpPHp6UXk7fRREJknSGO75k3eym', 'assocmalvadin'),
(45, '99999999999', '2014-09-09 17:15:08', 'party@aperture.com', 'party', '$2a$08$yTCqrGftLGKlmATXjUDvPeD6TBrVEyyfk9zHDx0H73r2ldVpGgL16', 'Party escort associate'),
(46, '99999999999', '2014-09-09 17:16:03', 'ahnemvemcomessaquehojeeunaotoprachacotanaohein@gmail.ha', 'SLIDEINTOTHESEA', '$2a$08$XWmhq3nURj.aAEulxYfuVOvsJKpct7A5CZOfBhWQIECijZuAGIXwu', 'Associado que não tá nem aí'),
(47, '99999999999', '2014-09-10 11:29:07', 'asdf@fdsa.com', 'aluno3', '$2a$08$TfjQjl0FISutKSXPelT5nuJDU703QwatYGBefY7wlmGJBHmYTraIm', 'aluno3'),
(48, '99999999999', '2014-09-11 18:12:27', 'OIOIOI@IOI.COI', 'UMDOIS', '$2a$08$EoKryMDBGDUBsY6jEUojkOqzHRcyKVNEfbU0eZQ4xwQJjRysu0j5.', 'MEU DEUS QUE ADMIN LOCO É ESSE GALERA'),
(49, '99999999999', '2014-09-12 09:41:10', 'kizu@wa.jp', 'derp', '$2a$08$0c60ZmZhDZB/CBLgOsRomuSkY5Yh7fng/TFX/IMxvaO32V9n8mQJe', 'Hontou no'),
(50, '99999999999', '2014-09-12 09:49:45', 'almejado@gmail.com', 'toto', '$2a$08$yUg.RCsSnNJPd2uNoUlFge9yU6D8Mm02hmAEiOYO5DZW27NbXjtSG', 'Totó'),
(51, '99999999999', '2014-09-12 09:55:33', 'hahaaa@gmail.com', 'true', '$2a$08$0O02xDXKkonT5zVeOhhxSOyuWcqpmy/dlG8LwZ.6VMyVqb1WPz5GK', 'aaa'),
(52, '99999999999', '2014-09-12 10:16:55', 'associ@doque.CALAABOCA', 'assossiassaos', '$2a$08$tFSoIMWnSiXcPC9XERsGUOG48eiYs5v.UIoWpJwxLpNTjFwNjX0v2', 'Associado que chegou assim de repente nossa'),
(53, '99999999999', '2014-09-16 11:19:00', 'asdf@fdsa.com', '2testetas', '$2a$08$WiVdH7XqPiubFb6J1KRzhemCJpKuZLmxlts6/sDwlfHaTO4BnXqXy', 'testetas'),
(54, '99999999999', '2014-09-17 13:05:44', 'leu@xn--1caaaa9gaaa.com', 'associacao', '$2a$08$7XfsLg51M3VdhBmLuiJuMu5dg5YpJqtfOS3FjyMYxjX20dyyUjjdy', 'Assssssssociadosão');

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
