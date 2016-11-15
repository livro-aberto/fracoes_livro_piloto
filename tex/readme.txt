O livro de frações pode ser gerado apenas a versão do aluno ou o livro do aluno com o livro do professor. 

Para gerar o livro do aluno:
Compile os arquivos livro_aluno_completo.tex e introdução.tex
Depois junte os pdfs na seguinte ordem capa, blank, contra-capa, introducao, livro_aluno_completo.

Para gerar o livro do professor:
Compile os arquivos livro_aluno_completo.tex, livro_professor_completo.tex e introdução.tex. Então é necessário alternar as páginas do livro do aluno com as do professor. O seguinte comando faz isso no Linux

pdftk A=livro_aluno_completo.pdf B=livro_professor_completo.pdf shuffle B A output collated.pdf


Depois retire a última página (branca) da introdução e junte os pdfs na seguinte ordem capa, blank, contra-capa, blank, introducao, collated.pdf.

As figuras do livro estão em tex/livro/media/capX/secoes/png ou pngs_licaoX.
