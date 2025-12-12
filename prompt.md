# Aplicativo de Flashcards para Estudo de Japonês - Documento de Requisitos

##1. Informações Básicas

### 1.1 Nome do Aplicativo
\n日本語 Flashcards

### 1.2 Descrição do Aplicativo

Aplicativo SPA (Single Page Application) para estudo da língua japonesa através de flashcards, desenvolvido com HTML, CSS e JavaScript puro, sem dependências externas. Funciona diretamente no navegador e permite salvar dados localmente em formato JSON.

## 2. Funcionalidades Principais

### 2.1 Gerenciamento de Grupos

- Criar novos grupos de decks
- Editar nome e configurações de grupos existentes
- Excluir grupos (com confirmação)
- Visualizar lista de grupos organizados
- Importar deck para dentro de um grupo existente

### 2.2 Gerenciamento de Decks

- Criar novos decks dentro de grupos
- Editar informações de decks (nome, descrição)
- Excluir decks (com confirmação)
- Organizar decks em grupos
- Voltar para lista de decks

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
- Botão 'Ouvir' com TTS (Text-to-Speech) utilizando sempre o texto do campo hiragana/katakana
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

### 2.7 Medidor de espaço disponível

- Medir espaço disponível na pasta local
- Exibir mensagem de alerta quando espaço insuficiente

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

## 4. Interfacedo Usuário

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

## 5. Estilo de Design

- Paleta de cores: tons neutros bege (#E8E4D9) como fundo, azul (#4A90E2) para botões primários, cinza escuro (#2C3E50) para textos
- Tipografia: fonte sans-serif limpa e legível, tamanho grande para o texto dos cards
- Layout: design centrado com card em destaque, botões organizados em barras horizontais
- Elementos visuais: botões com bordas arredondadas, sombras suaves para profundidade, ícones intuitivos nos botões
- Responsividade: interface adaptável para desktop e dispositivos móveis

## 6. Requisitos Técnicos

- Tipo: SPA (Single Page Application)
- Tecnologias: HTML5, CSS3, JavaScript puro
- Sem frameworks ou bibliotecas externas
- Compatível com navegadores modernos (Chrome, Firefox, Safari, Edge)
- Funcionalidade offline após carregamento inicial
- API Web Speech para TTS
- File System Access API para importação/exportação de arquivos
- Não utilizar Node.js
- Gerar apenas um arquivo flashcards.html (standalone html)