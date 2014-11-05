-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
<<<<<<< HEAD
-- Generation Time: Nov 04, 2014 at 03:37 PM
=======
-- Generation Time: Nov 03, 2014 at 04:04 PM
>>>>>>> c0caeebafbd1eed19073c1bf0bb2d9b30fe06f9c
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Administradores do sistema' AUTO_INCREMENT=32 ;

--
-- Dumping data for table `Administrador`
--

INSERT INTO `Administrador` (`idAdmin`, `idUsuario`, `nivel`, `corrigeTrabalho`, `permissoes`) VALUES
(1, 1, 'administrador', 0, 31),
(3, 3, 'professor', 0, 0),
(4, 4, 'coordenador', 0, 0),
(7, 9, 'administrador', 0, 0),
(8, 11, 'administrador', 0, 0),
(10, 27, 'administrador', 0, 0),
(11, 30, 'coordenador', 0, 0),
(12, 31, 'professor', 1, 0),
(13, 32, 'administrador', 0, 0),
(14, 40, 'professor', 1, 0),
(15, 41, 'professor', 1, 0),
(16, 42, 'professor', 0, 0),
(17, 48, 'administrador', 0, 0),
(18, 56, 'coordenador', 0, 0),
(19, 57, 'administrador', 0, 0),
(20, 58, 'administrador', 0, 0),
(21, 59, 'administrador', 0, 0),
(22, 60, 'administrador', 0, 0),
(24, 67, 'coordenador', 0, 0),
(25, 68, 'professor', 1, 0),
(29, 87, 'administrador', 0, 0),
(30, 88, 'administrador', 0, 15),
(31, 89, 'administrador', 0, 15);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aluno do curso' AUTO_INCREMENT=43 ;

--
-- Dumping data for table `Aluno`
--

INSERT INTO `Aluno` (`numeroInscricao`, `idUsuario`, `status`, `idIndicador`, `telefone`, `endereco`, `escolaridade`, `curso`, `cep`, `rua`, `numero`, `bairro`, `complemento`, `estado`, `cidade`, `pais`) VALUES
(3, 12, 'inscrito', NULL, '2147483647', 'Avenida B, 303, Apto 400, Belo Horizonte, Minas Gerais', 'doutorado', 'Aquacultura', '30880420', 'Avenida B', 303, 'Bairro Aleatório', 'Apto 400', 'MG', 'Belo Horizonte', 'BRL'),
(4, 13, 'inscrito', NULL, '2147483647', 'Rua A Cidade B', 'médio completo', NULL, '0', '', 0, '', '', '', '', ''),
(6, 15, 'inscrito', NULL, '319988776611', 'Avenida bairro cidade estado', 'superior incompleto', 'Ciência da Computação', '0', '', 0, '', '', '', '', ''),
(7, 17, 'inscrito', NULL, '1212123434', 'Minha casa', 'superior completo', 'Ciência da Computação', '0', '', 0, '', '', '', '', ''),
(9, 19, 'inscrito', NULL, '3166554433', 'サボテン', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(10, 20, 'inscrito', NULL, '3144556677', 'asdfsjhgffds', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(11, 23, 'inscrito', 4, '2121212121', 'saddsasdsdadsaasdadssda', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(12, 24, 'inscrito', NULL, '9999999999', 'sadkfçlfjçlk', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(13, 25, 'inscrito', NULL, '2222222222', 'テストテストテストテスト', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(14, 29, 'inscrito', NULL, '3199887766', 'Avenida B, 303, Apto 400, Belo Horizonte, Minas Gerais', 'fundamental incompleto', NULL, '30880420', 'Avenida B', 303, 'Um bairro random', 'Apto 400', 'MG', 'Belo Horizonte', 'BRL'),
(15, 33, 'preinscrito', NULL, '3189987777', 'saddsaadsdsa', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(16, 34, 'preinscrito', NULL, '3199887766', 'teste ende', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(18, 36, 'preinscrito', NULL, '3199887766', 'asdfasdf', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(19, 37, 'inscrito', 4, '3199999999', 'Mão', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(20, 38, 'preinscrito', NULL, '3112345432', 'asdfasdf', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(21, 39, 'preinscrito', NULL, '3199887766', 'Rua da rua da', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(22, 47, 'inscrito', 14, '3199887766', 'asdfasdf', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(23, 49, 'preinscrito', NULL, '3199887766', 'itamanai yo', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(24, 50, 'preinscrito', NULL, '3199887766', 'Saiba que o problema é seu (I pray for children down in Aaaaaaaaaaafricaaaaaaaaaaaa)', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(25, 51, 'preinscrito', NULL, '3188776655', 'Haaa dsadkjaskda', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(26, 53, 'preinscrito', NULL, '3199887766', 'asdfasdf', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(27, 62, 'preinscrito', NULL, '3199887766', 'asdfsjhgffds', 'superior completo', 'Ciência da Computação', '30880420', 'adfasdf', 12, 'asdfasdf', 'apt 101', 'MG', 'asdfasdf', 'BRL'),
(28, 63, 'preinscrito', NULL, '3199887766', 'asdfasdf', 'mestrado', 'asdfasd', '0', '', 0, '', '', '', '', ''),
(29, 64, 'preinscrito', NULL, '3134737678', 'asdfasdf', 'médio incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(30, 65, 'preinscrito', NULL, '3199999999', 'saddsasdsdadsaasdadssda', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(31, 66, 'preinscrito', NULL, '3199887766', 'asdfsjhgffds', 'fundamental incompleto', NULL, '0', '', 0, '', '', '', '', ''),
(32, 69, 'preinscrito', NULL, '3199887766', 'asdfsasdf sa 21', 'fundamental incompleto', NULL, '12312123', 'LET THE SKY FALLLL', 203, 'SKYFAALLL', 'WHEN IT CRUM-BLEEES', 'AL', 'WHEN IT CRUMBLESSSS', 'BRL'),
(38, 78, 'preinscrito', NULL, '3199887766', '', 'fundamental incompleto', NULL, '0', '', -1, '', '', '', '', ''),
(39, 79, 'preinscrito', NULL, '3199887766', '', 'fundamental incompleto', NULL, '30880420', 'adfasdf', 12, 'fadsfa', 'adfasdf', 'AC', 'asdfads', 'BRL'),
(40, 80, 'preinscrito', NULL, '3199887766', '', 'fundamental incompleto', NULL, '30880420', 'Treze', 13, 'Cruz das Treze', 'ap 1313', 'AP', 'Treze Marias', 'BRL'),
(41, 85, 'preinscrito', NULL, '3112345432', '', 'fundamental incompleto', NULL, '30880420', 'asdfasdf', 12, 'asdfads', 'asdfasdf', 'CE', 'fasdfasdf', 'BRL'),
(42, 86, 'preinscrito', NULL, '3199887755', '', 'fundamental incompleto', NULL, '30880420', 'abdcj', 23, 'mkpooiwl', 'kdlddlld', 'AC', 'ldsao', 'BRL');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Artigo ou noticia a ser mostrada no site' AUTO_INCREMENT=24 ;

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
(18, 'ルカス', '日本語', 'すごいね！ このシステム日本語の言葉', '2014-07-29 09:38:39', 'noticia'),
(19, 'Teste', 'Teste', 'Apenas testando', '2014-08-01 09:02:19', 'artigo'),
(20, 'Datarock', 'Computer Camp Love', 'I ran into her on computer camp', '2014-09-04 16:12:58', 'artigo'),
(21, 'eue', 'me upé', 'sadadsdasasdsdaasdsda', '2014-09-24 10:49:03', 'noticia'),
(22, 'asdfasdf', 'asdfasdf', 'asdfasdf', '2014-10-09 00:00:00', 'artigo'),
(23, 'sdfasdfasdf', 'asdfasdfasdf', 'asdfasdfasdf', '2014-10-30 00:00:00', 'artigo');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Associado da CONAHOM/ATENEMG' AUTO_INCREMENT=18 ;

--
-- Dumping data for table `Associado`
--

INSERT INTO `Associado` (`idAssoc`, `idUsuario`, `instituicao`, `formacaoTerapeutica`, `telefone`, `endereco`, `cidade`, `estado`, `numObjeto`, `dataEnvioCarteirinha`, `enviouDocumentos`, `cep`, `rua`, `numero`, `bairro`, `complemento`, `pais`) VALUES
(2, 28, 'atenemg', 'Abacate homeopático', '3199887766', 'dsasdadsa', 'Belzont', 'MG', 'you were standing in the street cuz you were trying not to crack up', '2007-04-07', 1, '12312123', '31231321', 32, '2323423', '323223', 'BRL'),
(3, 43, 'conahom', '', '3166600666', 'Maltat', '', '', NULL, NULL, 1, '0', '', 0, '', NULL, ''),
(5, 45, 'atenemg', '', '3199886655', 'Aperture Science, 200. Perto da Black Mesa.', 'erasio', 'AP', 'uiuj7uk,juo8lu897', '2014-11-27', 1, '0', '', 0, '', NULL, ''),
(6, 46, 'conahom', 'teste', '3144444444', 'I SEE PEOPLE ON THE FLOOR', 'rvt', 'CE', '', '0000-00-00', 0, '0', '', 0, '', NULL, ''),
(7, 52, 'atenemg', '', '3199884433', 'Rua da rua da', 'Hyrule', 'RO', NULL, NULL, 1, '0', '', 0, '', NULL, ''),
(9, 55, 'atenemg', '', '3112345432', 'asdfsasdf sa 21', 'Pallet', 'AC', NULL, NULL, 0, '0', '', 0, '', NULL, ''),
(10, 70, 'atenemg', 'santos dumont (quê?)', '3199999999', 'fdsafa', 'Tocagado', 'AC', '', '0000-00-00', 1, '0', '', 0, '', NULL, ''),
(11, 75, 'conahom', 'formado0 em honomatopoiea', '3132323232', 'sdfghçljk', 'çljkçkljçlkjkçl', 'AC', '', '1971-07-12', 0, '12321231', 'adfadsf', 231, 'asdfasdf', 'asdfadf', 'BRL'),
(12, 76, 'atenemg', 'I didn''nt ask for this pain', '3113311991', 'asdlçd', 'asdsad', 'AC', NULL, NULL, 0, '0', '', 0, '', NULL, ''),
(13, 77, 'conahom', 'eu amo uma tempestade mas eu não amo iluminamento', '3166666666', 'Rua dos Tra-sadfjhfbdfvdwaefr', 'I LAIAL', 'AC', '', '0000-00-00', 0, '0', '', 0, '', NULL, ''),
(14, 81, 'atenemg', 'Formação em terapia para terapeutas', '3199887766', '', 'fasdf', 'AC', NULL, NULL, 0, '0', '', 0, '', NULL, ''),
(15, 82, 'conahom', 'Terapia de Famosos', '3199887766', '', 'fasd', 'AC', NULL, NULL, 0, '0', '', 0, '', NULL, ''),
(16, 83, 'conahom', 'asdfdsds', '3131313131', '', 'fgsdf', 'AP', '', '1971-12-07', 0, '30880420', 'asdfa', 153, 'asdfa', 'adfas', 'BRL'),
(17, 84, 'atenemg', 'Teurapeta', '3199887766', '', 'dfa', 'AC', '', '1971-07-12', 0, '30880420', 'asdf', 45, 'asdfa', 'fasdf', 'BRL');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Aula lancada no sistema' AUTO_INCREMENT=34 ;

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
(16, 4, 1, '2014-03-08 15:30:00', 12, NULL, 'Aerodynamic'),
(17, 4, 1, '2014-03-09 20:30:00', 3, 0, ''),
(18, 13, 1, '2014-09-09 13:00:00', 12, NULL, 'Êêêê ôôô, vida de gado'),
(19, 4, 1, '2014-07-17 03:00:00', 14, 0, ''),
(20, 2, 2, '2013-05-05 12:12:00', 3, 66.6667, ''),
(21, 4, 1, '2014-09-09 12:22:00', 14, 33.3333, ''),
(22, 4, 1, '2014-04-03 09:38:00', 14, 0, ''),
(23, 4, 1, '2014-04-03 09:38:00', 14, NULL, 'Finge que sabe a matéria e ensina alguma coisa. Sei lá, finge que ensina PLA, PAL, complemento... *fica sem ar* de dois...\r\n\r\nEnsina que -32 = 32\r\n\r\nDá um TP sobre Verilog I WAS SOLID GOLD'),
(24, 18, 1, '2002-07-18 09:00:00', NULL, NULL, 'adfhsd'),
(25, 20, 1, '2014-09-10 01:00:00', NULL, NULL, 'Aula para ensinar o basico para se tornar um mestre pokemon e entregar o seu primeiro pokemon (não tem pikachu).'),
(26, 22, 1, '2014-08-06 10:00:00', 25, 33.3333, 'Aula ja passou bando de bobo'),
(27, 22, 1, '2014-10-01 14:00:00', 25, NULL, 'Ainda vou ter q da essa aula pqp'),
(28, 22, 1, '2014-10-28 14:00:00', 25, NULL, 'Quem pode pode quem não pode se fode'),
(29, 11, 3, '2003-01-24 09:00:00', 14, NULL, 'asdfasdf'),
(30, 11, 3, '2003-01-24 09:00:00', 14, NULL, 'asdfasdf'),
(31, 3, 1, '2014-09-02 13:30:00', NULL, NULL, 'TESTE'),
(32, 3, 1, '2014-02-02 12:22:00', NULL, NULL, 'tata'),
(33, 3, 1, '2014-09-02 13:33:00', NULL, NULL, 'adsdsaads');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Oferta de curso em determinada cidade em determinado período' AUTO_INCREMENT=23 ;

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
(19, 'AM', 2003, 'abacartea<br><br></select> <!--', 4, 'floresta', 67, 76, '2030-09-09', 'Homebros', '81535238000113'),
(20, 'AC', 2014, 'Pallet', 11, 'Oak Lab', 500, 100, '2014-07-24', 'umaí', '91558557000106'),
(21, 'MG', 2015, 'Belo Horizonte', 11, 'UFMG', 75, 125, '2014-09-27', 'anonimous', '18305845000150'),
(22, 'AC', 2014, 'Tocagado', 24, 'UFíGado', 75, 125, '2014-07-10', 'Tocagado Company', '26541641000147');

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
(1, '2014-07-28 08:40:23', '2011-08-20 14:00:00', 'teste sql', 'abcdefé', 'adsadsadssdasadsadasdsdasad'),
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
(6, 15, 0, 1),
(9, 15, 1, 1),
(10, 15, 1, 1),
(11, 15, 1, 1),
(18, 15, 1, 0),
(15, 16, 1, 1),
(18, 16, 1, 0),
(19, 16, 1, 1),
(19, 17, 1, 0),
(27, 17, 1, 1),
(19, 19, 1, 0),
(27, 19, 1, 1),
(14, 20, 1, 1),
(19, 21, 1, 0),
(27, 21, 1, 1),
(19, 22, 1, 0),
(27, 22, 1, 1),
(19, 23, 0, 0),
(27, 23, 0, 0),
(32, 26, 1, 1),
(7, 32, 0, 0),
(11, 32, 1, 0),
(14, 32, 0, 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Livros a venda no sistema' AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Livro`
--

INSERT INTO `Livro` (`idLivro`, `valor`, `quantidade`, `nome`, `autor`, `editora`, `dataPublic`, `edicao`, `fornecedor`) VALUES
(1, 123, 1, 'O guia do guia', 'Thomas the tank engine', 'aaaaaaaaaaaa', '2016-05-16', 1, 'aaaaaaaaaaaaaaaaaaa'),
(2, 161.5, 5, 'Tratado de Homeopatia', 'Pierre Cornilot', 'Artmed', '2014-06-16', 1, 'Andarilho'),
(3, 125.52, 100, 'Vade-mécum da Prescrição em Homeopatia', 'Horvilleur, Alain / ANDREI', 'umaí', '2014-10-28', 1, 'Aquelelá');

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
<<<<<<< HEAD
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Matrícula de um aluno em uma etapa em determinado período' AUTO_INCREMENT=120 ;
=======
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Matrícula de um aluno em uma etapa em determinado período' AUTO_INCREMENT=119 ;
>>>>>>> c0caeebafbd1eed19073c1bf0bb2d9b30fe06f9c

--
-- Dumping data for table `Matricula`
--

INSERT INTO `Matricula` (`idMatricula`, `chaveAluno`, `etapa`, `aprovado`, `chaveCidade`) VALUES
(64, 19, 1, NULL, 4),
(65, 18, 1, NULL, 4),
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
(82, 26, 1, NULL, 20),
(83, 14, 3, NULL, 21),
(85, 27, 1, NULL, 21),
(88, 23, 1, NULL, 21),
(89, 31, 1, NULL, 20),
(95, 14, 3, NULL, 11),
(100, 42, 1, NULL, 11),
(101, 14, 1, NULL, 12),
(103, 32, 1, NULL, 22),
(106, 15, 3, NULL, 4),
(107, 16, 2, NULL, 4),
(108, 42, 1, NULL, 12),
(109, 42, 4, NULL, 2),
(110, 42, 1, NULL, 4),
(111, 42, 1, NULL, 21),
(112, 27, 1, NULL, 4),
(113, 41, 1, NULL, 4),
(115, 41, 1, NULL, 11),
(116, 41, 1, NULL, 12),
<<<<<<< HEAD
(118, 14, 1, NULL, 3),
(119, 22, 1, NULL, 13);
=======
(118, 14, 1, NULL, 3);
>>>>>>> c0caeebafbd1eed19073c1bf0bb2d9b30fe06f9c

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
<<<<<<< HEAD
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Representa uma notificação a ser mostrada para o aluno na página principal' AUTO_INCREMENT=13 ;
=======
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Representa uma notificação a ser mostrada para o aluno na página principal' AUTO_INCREMENT=12 ;
>>>>>>> c0caeebafbd1eed19073c1bf0bb2d9b30fe06f9c

--
-- Dumping data for table `Notificacao`
--

INSERT INTO `Notificacao` (`idNotificacao`, `titulo`, `texto`, `chaveAluno`, `lida`) VALUES
(1, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$23.00\nData: 31/10/2014\nHorário: 19:52\nMétodo: Dsadsdasdsa', 14, 1),
(2, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$34.00\nData: 03/11/2014\nHorário: 12:51\nMétodo: Weew', 14, 1),
(3, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$11.00\nData: 03/11/2014\nHorário: 12:51\nMétodo: Eweew', 14, 1),
(4, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade.', 7, 0),
(5, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade.', 14, 1),
(6, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$12.00\nData: 03/11/2014\nHorário: 15:09\nMétodo: Dsasaddas', 14, 1),
(7, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$10.00\nData: 03/11/2014\nHorário: 15:38\nMétodo: Qualquer cosia', 14, 1),
(8, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$32.00\nData: 03/11/2014\nHorário: 15:41\nMétodo: Sdaasd', 14, 1),
(9, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$32.00\nData: 03/11/2014\nHorário: 15:41\nMétodo: Dsasd', 14, 1),
(10, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade.', 7, 0),
<<<<<<< HEAD
(11, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade.', 14, 1),
(12, 'Pagamento recebido', 'Pagamento recebido:\nValor: R$98.00\nData: 04/11/2014\nHorário: 09:36\nMétodo: Dinheiro', 22, 0);
=======
(11, 'Ausência na aula do dia 02/02/2014', 'Uma ausência sua foi registrada para a aula do dia 02/02/2014\nCaso esse dado não esteja correto, favor contatar o coordenador da sua cidade.', 14, 1);
>>>>>>> c0caeebafbd1eed19073c1bf0bb2d9b30fe06f9c

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Pagamento da anuidade de um associado' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `PgtoAnuidade`
--

INSERT INTO `PgtoAnuidade` (`idPagAnuidade`, `chaveAssoc`, `inscricao`, `valorTotal`, `valorPago`, `metodo`, `data`, `ano`, `fechado`) VALUES
(1, 2, 1, 123, 1, 'sdsfdf', '2014-11-06 00:00:00', 2014, 1),
(2, 2, 1, 122, 12, 'dasa', '2014-11-01 00:00:00', 1998, 1),
(3, 2, 0, 123, 12, 'sdasdsad', '2014-10-28 00:00:00', 1998, 1),
(4, 2, 0, 123, 12, 'sadsdasa', '2014-11-01 00:00:00', 2014, 0);

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
<<<<<<< HEAD
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Pagamento de mensalidade ou inscricao de aluno' AUTO_INCREMENT=97 ;
=======
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Pagamento de mensalidade ou inscricao de aluno' AUTO_INCREMENT=85 ;
>>>>>>> c0caeebafbd1eed19073c1bf0bb2d9b30fe06f9c

--
-- Dumping data for table `PgtoMensalidade`
--

INSERT INTO `PgtoMensalidade` (`idPagMensalidade`, `chaveMatricula`, `numParcela`, `valorTotal`, `valorPago`, `desconto`, `metodo`, `data`, `ano`, `fechado`) VALUES
(1, 112, 0, 6500, 0, 0, '', NULL, 2014, 0),
(2, 112, 1, 150, 0, 0, '', NULL, 2014, 0),
(3, 112, 2, 150, 0, 0, '', NULL, 2014, 0),
(4, 112, 3, 150, 0, 0, '', NULL, 2014, 0),
(5, 112, 4, 150, 0, 0, '', NULL, 2014, 0),
(6, 112, 5, 150, 0, 0, '', NULL, 2014, 0),
(7, 112, 6, 150, 0, 0, '', NULL, 2014, 0),
(8, 112, 7, 150, 0, 0, '', NULL, 2014, 0),
(9, 112, 8, 150, 0, 0, '', NULL, 2014, 0),
(10, 112, 9, 150, 0, 0, '', NULL, 2014, 0),
(11, 112, 10, 150, 0, 0, '', NULL, 2014, 0),
(12, 112, 11, 150, 0, 0, '', NULL, 2014, 0),
(13, 113, 0, 6500, 6500, 0, 'teste', '2014-10-21 00:00:00', 2014, 1),
(14, 113, 1, 150, 150, 0, 'teste', '2014-10-21 00:00:00', 2014, 1),
(15, 113, 2, 150, 150, 0, 'cheque', '2014-10-21 00:00:00', 2014, 1),
(16, 113, 3, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(17, 113, 4, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(18, 113, 5, 150, 0, 0, '', NULL, 2014, 0),
(19, 113, 6, 150, 0, 0, '', NULL, 2014, 0),
(20, 113, 7, 150, 0, 0, '', NULL, 2014, 0),
(21, 113, 8, 150, 0, 0, '', NULL, 2014, 0),
(22, 113, 9, 150, 0, 0, '', NULL, 2014, 0),
(23, 113, 10, 150, 0, 0, '', NULL, 2014, 0),
(24, 113, 11, 150, 0, 0, '', NULL, 2014, 0),
(25, NULL, 0, 6500, 6500, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(26, NULL, 1, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(27, NULL, 2, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(28, NULL, 3, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(29, NULL, 4, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(30, NULL, 5, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(31, NULL, 6, 150, 150, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 1),
(32, NULL, 7, 150, 100, 0, 'dinheiro', '2014-10-21 00:00:00', 2014, 0),
(33, NULL, 8, 150, 0, 0, '', NULL, 2014, 0),
(34, NULL, 9, 150, 0, 0, '', NULL, 2014, 0),
(35, NULL, 10, 150, 0, 0, '', NULL, 2014, 0),
(36, NULL, 11, 150, 0, 0, '', NULL, 2014, 0),
(37, 115, 0, 123, 123, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(38, 115, 1, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(39, 115, 2, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(40, 115, 3, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(41, 115, 4, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(42, 115, 5, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(43, 115, 6, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(44, 115, 7, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(45, 115, 8, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(46, 115, 9, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(47, 115, 10, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(48, 115, 11, 12, 12, 0, 'dinheiro', '2014-10-21 00:00:00', 2003, 1),
(49, 116, 0, 122, 0, 0, '', NULL, 2005, 0),
(50, 116, 1, 44, 0, 0, '', NULL, 2005, 0),
(51, 116, 2, 44, 0, 0, '', NULL, 2005, 0),
(52, 116, 3, 44, 0, 0, '', NULL, 2005, 0),
(53, 116, 4, 44, 0, 0, '', NULL, 2005, 0),
(54, 116, 5, 44, 0, 0, '', NULL, 2005, 0),
(55, 116, 6, 44, 0, 0, '', NULL, 2005, 0),
(56, 116, 7, 44, 0, 0, '', NULL, 2005, 0),
(57, 116, 8, 44, 0, 0, '', NULL, 2005, 0),
(58, 116, 9, 44, 0, 0, '', NULL, 2005, 0),
(59, 116, 10, 44, 0, 0, '', NULL, 2005, 0),
(60, 116, 11, 44, 0, 0, '', NULL, 2005, 0),
(61, NULL, 0, 560.8, 0, 0, '', NULL, 2014, 0),
(62, NULL, 1, 120.9, 0, 0, '', NULL, 2014, 0),
(63, NULL, 2, 120.9, 0, 0, '', NULL, 2014, 0),
(64, NULL, 3, 120.9, 0, 0, '', NULL, 2014, 0),
(65, NULL, 4, 120.9, 0, 0, '', NULL, 2014, 0),
(66, NULL, 5, 120.9, 0, 0, '', NULL, 2014, 0),
(67, NULL, 6, 120.9, 0, 0, '', NULL, 2014, 0),
(68, NULL, 7, 120.9, 0, 0, '', NULL, 2014, 0),
(69, NULL, 8, 120.9, 0, 0, '', NULL, 2014, 0),
(70, NULL, 9, 120.9, 0, 0, '', NULL, 2014, 0),
(71, NULL, 10, 120.9, 0, 0, '', NULL, 2014, 0),
(72, NULL, 11, 120.9, 0, 0, '', NULL, 2014, 0),
(73, 118, 0, 560.8, 560.8, 0, 'Cara, dinheiros|Sdadsa', '2014-10-30 00:00:00', 2014, 1),
(74, 118, 1, 120.9, 120.9, 0, 'Sdadsa|Monkey|Dindin', '2014-10-31 00:00:00', 2014, 1),
(75, 118, 2, 120.9, 120.9, 0, 'Dindin|Dasdsa', '2014-10-31 00:00:00', 2014, 1),
(76, 118, 3, 120.9, 120.9, 0, 'Dasdsa', '2014-10-31 00:00:00', 2014, 1),
(77, 118, 4, 120.9, 120.9, 0, 'Dasdsa|Eqewq|Dsadsdasdsa|Weew', '2014-11-03 00:00:00', 2014, 1),
(78, 118, 5, 120.9, 106.6, 0, 'Weew|Eweew|Dsasaddas|Qualquer cosia|Sdaasd|Dsasd', '2014-11-03 00:00:00', 2014, 0),
(79, 118, 6, 120.9, 0, 0, '', NULL, 2014, 0),
(80, 118, 7, 120.9, 0, 0, '', NULL, 2014, 0),
(81, 118, 8, 120.9, 0, 0, '', NULL, 2014, 0),
(82, 118, 9, 120.9, 0, 0, '', NULL, 2014, 0),
(83, 118, 10, 120.9, 0, 0, '', NULL, 2014, 0),
<<<<<<< HEAD
(84, 118, 11, 120.9, 0, 0, '', NULL, 2014, 0),
(85, 119, 0, 98, 98, 0, 'Dinheiro', '2014-11-04 00:00:00', 2014, 1),
(86, 119, 1, 89, 0, 0, '', NULL, 2014, 0),
(87, 119, 2, 89, 0, 0, '', NULL, 2014, 0),
(88, 119, 3, 89, 0, 0, '', NULL, 2014, 0),
(89, 119, 4, 89, 0, 0, '', NULL, 2014, 0),
(90, 119, 5, 89, 0, 0, '', NULL, 2014, 0),
(91, 119, 6, 89, 0, 0, '', NULL, 2014, 0),
(92, 119, 7, 89, 0, 0, '', NULL, 2014, 0),
(93, 119, 8, 89, 0, 0, '', NULL, 2014, 0),
(94, 119, 9, 89, 0, 0, '', NULL, 2014, 0),
(95, 119, 10, 89, 0, 0, '', NULL, 2014, 0),
(96, 119, 11, 89, 0, 0, '', NULL, 2014, 0);
=======
(84, 118, 11, 120.9, 0, 0, '', NULL, 2014, 0);
>>>>>>> c0caeebafbd1eed19073c1bf0bb2d9b30fe06f9c

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Dados de reunião a serem mostrados no site' AUTO_INCREMENT=13 ;

--
-- Dumping data for table `Reuniao`
--

INSERT INTO `Reuniao` (`idReuniao`, `tema`, `data`, `descricao`, `local`) VALUES
(6, 'Reunião', '2013-08-15 00:00:00', 'Nessa reunião serão abordados temas homeopáticos d ehomeopatia homeopática homeopatosa. Para acompanhar o conteúdo é necessário ter vindo nas reuniões de 05/06/2007 e de 30/03/2014. Serão abordados vários temas, como o site novo que está quase pronto, apesar de não ser esse o objetivo da reunião, ora bolas.', 'Univerdidade univerdal do univerdo'),
(7, 'fdfasddsfa', '2014-08-03 08:36:23', 'fdadffdsa', 'fdsafdsadafsfsda'),
(10, 'Reunião dos atrasados', '2014-09-09 07:00:00', 'A reunião para pessoas que se atrasam demais. Na verdade a reunião começa às 19 horas, mas já sabemos que vocês vão atrasar, né.\r\n\r\nAnd I think I found it', 'UFMJ'),
(11, 'Reunião em casa', '2014-10-15 09:00:00', 'Reunião para tentar ensinar a matéria que ele não sabe para ele poder ensinar.', 'Casa do Não sei a Matéria'),
(12, 'Reunião com o fim de mudar o nome da cidade', '2014-10-15 10:00:00', 'Abertura para pedido de troca do nome da cidade de Tocagado para Tomi Jado.', 'UFGado');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Trabalho enviado por aluno' AUTO_INCREMENT=20 ;

--
-- Dumping data for table `Trabalho`
--

INSERT INTO `Trabalho` (`idTrabalho`, `chaveAluno`, `dataEntrega`, `chaveDefinicao`, `nota`, `comentarioProfessor`, `extensao`) VALUES
(1, 14, '2014-09-24 10:37:09', 5, 76, '454345535545', 'doc'),
(4, 14, '2014-09-24 10:37:09', 4, 1, 'AHA UHUL O 1 É O NOVO 57', 'doc'),
(5, 14, '2014-09-24 10:37:09', 6, 42, 'oi guri ,seu trabái precisa de mais respostas (vide nota) :3', 'doc'),
(6, 14, '2014-09-24 10:37:09', 7, NULL, NULL, 'doc'),
(7, 16, '2014-09-10 11:26:48', 5, NULL, NULL, 'txt'),
(8, 22, '2014-09-10 11:31:36', 7, NULL, NULL, 'txt'),
(9, 15, '2014-09-10 16:20:05', 7, 0, 'MI RÉSPEITE VÉI SEINVERGONHA', 'txt'),
(10, 14, '2014-09-24 10:37:09', 9, 0, 'ha muluque', 'doc'),
(15, 14, '2014-09-24 10:37:09', 15, 23, 'como se não houvesse amanhã', 'doc'),
(16, 32, '2014-09-29 11:01:40', 18, NULL, NULL, 'txt'),
(17, 32, '2014-09-29 11:01:47', 19, 100, 'OP!', 'txt'),
(18, 14, '2014-10-02 10:34:22', 20, NULL, NULL, 'txt'),
(19, 14, '2014-10-31 17:10:09', 19, 50, 'Jorel', 'pdf');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Especificacao dada para a confeccao de um trabalho por parte dos alunos' AUTO_INCREMENT=21 ;

--
-- Dumping data for table `TrabalhoDefinicao`
--

INSERT INTO `TrabalhoDefinicao` (`idDefTrabalho`, `titulo`, `etapa`, `descricao`, `dataLimite`, `ano`) VALUES
(4, 'Homeopatia prática escolástica', 2, 'Fazer uma apresentação do modelo aristotélico-platônico do teorema de Fermat. Mesmo que isso não faça sentido algum.', '2014-08-19 00:00:00', 2014),
(5, 'Teste', 2, 'fdgjlklççl', '2014-09-02 00:00:00', 2014),
(6, 'Trabalho atrasado', 3, 'Favor entregar esse trabalho atrasado. Porque aparentemente isso é legal', '2014-02-02 00:00:00', 2014),
(7, 'Teste 2', 3, 'Tava aqui sem nada pra fazer, e decidi fazer pedir esse trabalho , vlw, flw.', '2014-11-27 00:00:00', 2014),
(9, 'Teste agaom', 3, 'asdf', '2014-09-25 00:00:00', 2014),
(15, 'É preciso saber fazer', 3, 'teste', '2014-10-23 00:00:00', 2014),
(16, 'Bomba', 3, 'Se enviar um arquivo é bomba.', '2014-11-27 00:00:00', 2014),
(17, 'Trabalho padrão para recém matriculados', 1, 'Sifuderu primeiro dia de aula com trabalho pra entregar', '2014-09-29 00:00:00', 2014),
(18, 'Cura para gadisse para iniciantes', 1, 'Sei lá o q vamos ver nessa aula...', '2014-09-10 00:00:00', 2014),
(19, 'Aula Exemplar', 1, 'Aquela aula formosa, perfeita, escultural... ', '2014-10-14 00:00:00', 2014),
(20, 'Exame para testar seu passado', 2, 'Vamos testar o seu passado', '2013-07-18 00:00:00', 2013);

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Usuario do sistema, que pode ser aluno, associado ou administrador' AUTO_INCREMENT=90 ;

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
(23, '34983523774', '2014-08-05 13:58:18', 'luc.aug.freire@gmail.com', 'comcerteza', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'Ednaldo Pereira'),
(24, '34983523774', '2014-08-05 13:59:10', 'kdaosasdok@dsok.sad', 'jkçlfda', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'okdsadakos'),
(25, '11989183654', '2014-08-05 14:48:19', 'aaaa@aaaaaaa.aaaa', 'テストテ', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'テスト'),
(27, '11989183654', '2014-08-05 15:11:00', 'aaa@aaa.aaa', 'logo existo', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'tento'),
(28, '99999999999', '2014-08-05 15:49:01', 'aaa@aaa.aa', 'associado', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'assoc'),
(29, '99999999999', '2014-08-05 15:54:03', 'luc.aug.freire@gmail.com', 'aluno', '$2a$08$V9eCzv3d5CnRt2S.BFcs2uveSy8AkOXf1LjJ9YYdMkspk8YqT2nvO', 'aluno'),
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
(55, '99999999999', '2014-09-18 09:13:34', 'dsa@sda.ads', 'Outdoor', '$2a$08$eb/8g0UAaNJpypCPde6Rtu5BxC0awukq3OgB8xlMFxoQ/vsZjUhqm', 'Testudo'),
(56, '99999999999', '2014-09-18 11:25:33', 'preguiça@clube.com', 'nada', '$2a$08$ZfEd4mI22EBwupa0VdJkqur7irlZ1FpJNAv1F7XfR9aNzSgVvZQum', 'Coordenanada'),
(57, '99999999999', '2014-09-18 11:26:45', 'mari.oca@marioca.mar.io', 'Semideia', '$2a$08$8QlM7pw2pbNQq1VnYuWV8unH4y9.02FqGVx82DO0GoSYR9xX4o.L2', 'Marioca'),
(58, '99999999999', '2014-09-18 11:27:27', 'lou@coco.co', 'Loucoco', '$2a$08$u5UGmg9p0c.HhZOLXobXsuA8PJokeEXLFLRFuDONM.FMz/KNZLQnG', 'Loucoco'),
(59, '99999999999', '2014-09-18 11:28:03', 'cobreash@co.bra', 'Cobr', '$2a$08$oYtRjHnOIxQ1XF5HXTnV5OAI88QR1GzMUJ3ZHYNwR189iG/d6Pzdy', 'Cobrash'),
(60, '99999999999', '2014-09-18 11:29:34', 'nãoesonicpreto@so.nic', 'naoeknoclesemo', '$2a$08$8dtFsOvORA2mrcKH0BvzfuznbTulhoe/.OSpPOpuDX0x0.EQbhGKG', 'Shadow'),
(62, '99999999999', '2014-09-25 08:36:30', 'asdf@fdsa.com', 'matriculado', '$2a$08$gNoabOsMod4en7BhXdKPcuQvNv76gCk.UIJGclO7d8aH5FZDHJdui', 'matriculado'),
(63, '99999999999', '2014-09-25 09:15:21', 'asdf@fdsa.com', 'TesteSuperior', '$2a$08$qO1BflYnqrRwsAfccKdQnOKgj4.qJr3TxokQlDwXlKAg0/Vd4s9gG', 'asdfasdf'),
(64, '99999999999', '2014-09-25 09:20:18', 'asdf@fdsa.com', 'ieie', '$2a$08$r5O2RsDYj7Pm181yKlg7iOWC.1dNBBRn3seuZjtfRaxKBB1uDsVgq', 'Macumba'),
(65, '99999999999', '2014-09-25 09:22:27', 'test@faslsad.ksda', 'FOOOI', '$2a$08$WT/HqzgogMlqVV/nVJqV5eqi4O1CLzgng87spABXWq7cYcOdRnsUu', 'Agora Vai'),
(66, '34983523774', '2014-09-25 09:39:29', 'test@faslsad.ksda', 'testetel', '$2a$08$de4/6cg.iM.VnR04uOOG7OE/aTAaf/IQVZy0MtAOrjaW18qx3yTDC', 'testetel'),
(67, '99999999999', '2014-09-29 10:34:48', 'asdf@fdsa.com', 'CoordenadorExemplar', '$2a$08$wAvnraz8DyJhhDGq.T8Z4eZfdHmbxp5lyyFGuVDD8GTe2zQMTAcyi', 'Coordenador teste'),
(68, '99999999999', '2014-09-29 10:36:10', 'fsdaasd@saf.cini', 'ProfessorExemplar', '$2a$08$0thoEX7el9cVaMaL2oYRYemkZn/qn8scaCilMfjUJCnByAQMTCwWu', 'Professor Teste'),
(69, '99999999999', '2014-09-29 10:39:53', 'test@faslsad.ksda', 'AlunoExemplar', '$2a$08$FtpNc4zP4YbKFYAc4ZPbtu6hWVYG72wGSL2bKEmBeBl./KkT8o5xu', 'SALGADO de Jimon'),
(70, '99999999999', '2014-09-29 10:41:33', 'kdaosasdok@dsok.sad', 'AssociadoExplorer', '$2a$08$cU7wsy0fTSpIMiHw5aeMYOwRjt8jHaBfbSwUmLuYbYZmRAyzE/A8u', 'Associadaço'),
(75, '99999999999', '2014-10-01 15:15:16', 'ldas@sda.adscoasd', 'lores', '$2a$08$fo6Mwkd4TJ/qbgS340BZHesPN5k6/oDhGa9qpzgx862nfCQ/0IMG2', 'Testinha'),
(76, '99999999999', '2014-10-01 15:22:07', 'sustenido@fi.com', 'it just came over me', '$2a$08$chK7PU7pzBOpFF6s8RpJbes6VdsucWnGsLp1xtTx/HI.YWzrYzD02', 'Atenemangão'),
(77, '99999999999', '2014-10-01 15:51:38', 'iliketr@in.s', 'moco', '$2a$08$DpbFQ8IWyCrbmAgNPXURuu/wgyxwVRl1jEXsZYyHOdbWpRwau9e/S', 'asdf'),
(78, '99999999999', '2014-10-02 11:32:45', 'asdf@fdsa.com', 'testestestesa', '$2a$08$fJd0hl/PKd7IVlXK9gveFesXjK/4rJHl7bkni.KXZwjOlbBydThoe', 'asdfas dasdf'),
(79, '99999999999', '2014-10-02 11:37:19', 'asdf@fdsa.com', 'testestea', '$2a$08$5JjthjDVhhX5F19Nh6Yz.O9GgK6BF01RLAltu85mF0/6zZSfEsExO', 'asdfacs'),
(80, '99999999999', '2014-10-06 08:43:30', 'asdf@fdsa.com', 'TestadoF', '$2a$08$Pr2pQXBqazygl77oE1heUu4vpY0qiYFT8Z5iTKR0bjRjwyynQVKty', 'Testado Souza Fio'),
(81, '99999999999', '2014-10-07 08:15:41', 'asdf@fdsa.com', 'Ascii', '$2a$08$vEoi.3WaJodO1sQMcv8tmuYpA09keTCrRsDIJuY90i1GgV7b1LS2S', 'associado associando associativo'),
(82, '99999999999', '2014-10-07 08:20:41', 'asdf@fdsa.com', 'Novin', '$2a$08$WnjtUCVwMyuqSct.2U0/ZuxjFqcrb7xRyLCF92ddlYQG31VRdZ1DC', 'um associado novo no pedaço'),
(83, '99999999999', '2014-10-07 08:33:42', 'asdf@fdsa.com', 'assssssa', '$2a$08$ArhmwDW8OBXW2B8jwXa3qOlNpIXT62twd.A2dyk7gkJ8k7NCoQ/.a', 'assoclown'),
(84, '99999999999', '2014-10-07 08:51:09', 'asdf@fdsa.com', 'Treura', '$2a$08$c5acfiscW9PUY8khZgun9.J5FUYPQxbR8eWuQymy5czhmy3tpul/i', 'associado Motorola'),
(85, '11989183654', '2014-10-07 10:42:14', 'asdfadsf@asdf.cas', 'Homeopatado', '$2a$08$MEY2RWnEDZnU1zSB1u.d1OOVZk1es00nf.tzL4l0ikzea0YkRz/UO', 'Homeopatando'),
(86, '99999999999', '2014-10-08 14:58:44', 'treS2fdk2@ednmio.com', 'sabugo', '$2a$08$WmF7WhcGqy.N0R/hmQQ1qOIWOXheJNGitRNJzBVmHY4fJ5dYVbyqe', 'Teste endereço novo'),
(87, '99999999999', '2014-10-13 09:26:39', 'asdf@fdsa.com', 'Hagatanga', '$2a$08$OE8eaq9MkakibpW45c9SqupkfkHmZl1pWP2eP..UNmZkdya42xCma', 'Hasga a Tanga'),
(88, '99999999999', '2014-10-13 09:29:59', 'asdf@fdsa.com', 'chitado', '$2a$08$HEPi.AMQEDb1BHd7FciSA.ej0NvHU1mjHmp9O8yR79I.5Ym2iaUiu', 'Chit'),
(89, '99999999999', '2014-10-13 09:30:50', 'asdfadsf@asdf.cas', 'essecarapodetudo', '$2a$08$E.KAl2YFVIJMnqOws0LYyeYrRThUi4UxhCX5uMtAl2G2c8sFr5TKW', 'pode tudo');

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
