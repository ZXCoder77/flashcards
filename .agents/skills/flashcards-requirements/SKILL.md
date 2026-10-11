---
name: flashcards-requirements
description: Documento de requisitos e diretrizes de desenvolvimento para o aplicativo de Flashcards para Estudo de Japonês. Este skill orienta a estrutura, design e funcionalidades do SPA de flashcards.
---

# Aplicativo de Flashcards para Estudo de Japonês - Documento de Requisitos

## 1. Informações Básicas

### 1.1 Nome do Aplicativo
日本語 Flashcards

### 1.2 Descrição do Aplicativo

Aplicativo SPA (Single Page Application) e PWA (Progressive Web App) instalável para estudo da língua japonesa através de flashcards, desenvolvido com HTML, CSS e JavaScript puro, sem dependências externas. Funciona diretamente no navegador ou instalado no dispositivo, com suporte offline completo e salvamento de dados localmente em formato JSON.

## 2. Funcionalidades Principais

### 2.1 Gerenciamento de Grupos

- Criar novos grupos de decks
- Editar nome e configurações de grupos existentes
- Excluir grupos (com confirmação)
- Visualizar lista de grupos organizados
- Importar deck para dentro de um grupo existente
- Adicionar grupo e deck de exemplo inicial na tela de estado vazio (Empty State)

### 2.2 Gerenciamento de Decks

- Criar novos decks dentro de grupos
- Editar informações de decks (nome, descrição)
- Excluir decks (com confirmação)
- Organizar decks em grupos
- Voltar para lista de decks
- Deck de exemplo inicial com frases dos verbos comer (食べる), ir (行く) e comprar (買う)

### 2.3 Gerenciamento de Cards

- Criar novos cards com quatro campos de texto:
  - Texto com kanji
  - Texto somente com hiragana ou katakana
  - Texto em romaji
  - Texto em português
- Editar cards existentes
- Excluir cards (com confirmação)
- Navegação entre cards (anterior/próximo)
- Visualizar progresso do estudo (ex: 'Card 2 de 1712%')

### 2.4 Funcionalidades de Estudo

- Alternar visualização entre Kanji, Hiragana, Rômaji e Português através de botões
- Função 'Misturar' para embaralhar ordem dos cards
- Função 'Reiniciar' para voltar ao início do deck
- Botão 'Ouvir' com TTS (Text-to-Speech) utilizando sempre o texto do campo selecionado em "Configurações de Voz"
- Barra de progresso visual do estudo

### 2.5 Importação e Exportação
- Exportar backup completo em formato JSON
- Importar backup completo em formato JSON
- Exportar grupo individual com os decks e cards em formato JSON
- Importar grupo individual com os decks e cards de arquivo JSON
- Exportar decks individuais com os cards em formato JSON
- Importar decks individuais com os cards de arquivo JSON
- Importar deck individual para dentro de um grupo existente
- Solicitar permissão do usuário para acesso ao sistema de arquivos local quando necessário
- Salvar dados automaticamente em pasta local (computador ou celular)

### 2.6 Configurações de Voz

- Seleção de voz para TTS (ex: Microsoft Haruka - Japanese)
- Ajuste de velocidade de reprodução (lento a rápido)
- Ajuste de tom de voz (grave a agudo)
- Seleção do campo a ser lido (Kanji, Hiragana, Rômaji, Português)

### 2.7 Medidor de espaço disponível

- Medir espaço disponível na pasta local
- Exibir mensagem de alerta quando espaço insuficiente

### 2.8 Recursos de PWA (Progressive Web App)

- Instalação no dispositivo (desktop e mobile) com modo de exibição standalone
- Funcionamento offline completo por meio de Service Worker e Cache Storage
- Detecção automática de atualizações (verificação periódica de hora em hora e em evento de visibilidade)
- Notificação em tela (toast) quando uma nova versão da aplicação estiver disponível
- Ação de atualização imediata (botão "Atualizar") acionando `SKIP_WAITING` e recarregamento da aplicação

## 3. Estrutura de Dados

### 3.1 Formato JSON dos Cards

Cada card contém:
- Texto com kanji
- Texto em hiragana/katakana
- Texto em romaji
- Texto em português

### 3.2 Armazenamento Local

- Dados salvos em formato JSON
- Estrutura hierárquica: Grupos > Decks > Cards
- Persistência em pasta local do dispositivo

## 4. Interface do Usuário

### 4.1 Referência Visual

Utilizar o modelo das imagens flashcards.png como base para o layout da interface.

### 4.2 Elementos da Interface

- Cabeçalho com título '日本語 Flashcards' e versão
- Barra de progresso visual
- Botões de ação: Editar Deck, Voltar para Decks, Misturar, Reiniciar
- Área central para exibição do card
- Botões de alternância: Kanji, Hiragana, Rômaji, Português
- Controles de navegação: Anterior, Próximo, Ouvir
- Painel de configurações de voz (expansível)
- Medidor de espaço disponível (expansível)
- Notificação de nova versão (toast de atualização PWA com botão Atualizar)
- Botões de ação no estado vazio (Empty State) para criação e carregamento de grupo/deck de exemplo

## 5. Estilo de Design

- Paleta de cores: tons neutros bege (#E8E4D9) como fundo, azul (#4A90E2) para botões primários, cinza escuro (#2C3E50) para textos
- Tipografia: fonte sans-serif limpa e legível, tamanho grande para o texto dos cards
- Layout: design centrado com card em destaque, botões organizados em barras horizontais
- Elementos visuais: botões com bordas arredondadas, sombras suaves para profundidade, ícones intuitivos nos botões
- Responsividade: interface adaptável para desktop e dispositivos móveis

## 6. Requisitos Técnicos

- Tipo: SPA (Single Page Application) e PWA (Progressive Web App)
- Tecnologias: HTML5, CSS3, JavaScript puro
- Sem frameworks ou bibliotecas externas
- Compatível com navegadores modernos (Chrome, Firefox, Safari, Edge)
- Suporte offline completo após carregamento inicial via Service Worker
- Arquitetura de arquivos PWA:
  - `index.html`: aplicação principal (SPA com CSS e JavaScript embutidos)
  - `manifest.json`: manifesto PWA (nome, tema #3498db, fundo #f5e6d3, modo `standalone` e ícones)
  - `sw.js`: Service Worker gerenciando cache estático (Cache Storage) e ciclo de vida de atualização com `SKIP_WAITING`
  - `icon-192.png` e `icon-512.png`: ícones da aplicação para desktop e mobile
- API Web Speech para TTS
- File System Access API para importação/exportação de arquivos
- Não utilizar Node.js
