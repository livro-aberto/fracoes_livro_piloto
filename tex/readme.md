A versão mais atual do pdf deste livro encontra-se em https://www.umlivroaberto.com/wp/?page_id=89

<b>Colaborar com o desenvolvimento deste material</b>

Para submeter mudanças diretamente por este repositório:
1. matenha a tecla Ctrl pressionada e clique no link https://github.com/livro-aberto/fracoes_livro_piloto/tree/master/tex
2. na aba que se abrirá, escolha o arquivo com extensão .tex que deseja alterar (por lição, texto do aluno ou do prof), 
3. clique em "Edit this file" (ícone de um lápis acima do texto, à direita),
4. efetue as edições que desejar,
5. faça um resumo da edição em "Propose file change" no final da página, e
6. na tela seguinte clique em "Create Pull Request" para nos enviar suas sugestões.

<b>Para administrar sua própria versão deste livro</b>

Deste livro pode ser gerada apenas a versão do aluno ou a do professor (que inclui o texto para o estudante). Em qualquer dos casos será necessário baixar os arquivos .tex, .sty e as figuras. Algumas fontes podem precisar ser instaladas no sistema.

O livro do aluno pode ser gerado apenas compilando o arquivo livro_aluno_completo.tex. Para usar a mesma fonte que utilizamos no original é necessário compilar com xelatex.

Para gerar o livro do professor: (DESATUALIZADO, por favor aguarde. Atualizaremos em breve)
Compile os arquivos livro_aluno_completo.tex, livro_professor_completo.tex e introdução.tex. Então é necessário alternar as páginas do livro do aluno com as do professor. O seguinte comando faz isso no Linux

pdftk A=livro_aluno_completo.pdf B=livro_professor_completo.pdf shuffle B A output collated.pdf

Depois retire a última página (branca) da introdução e junte os pdfs na seguinte ordem capa, blank, contra-capa, blank, introducao, collated.

As figuras do livro estão em tex/livro/media/capX/secoes/png ou pngs_licaoX.
