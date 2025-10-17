@ECHO OFF

rem update the user guide
D:\github\DevTools\docto -f "D:\github\DevTools-DEV\user guide.docx" -O "docs/user guide.pdf" -T wdFormatPDF
del D:\test-sites\dev\docs\user guide.pdf
copy docs/user guide.pdf D:\test-sites\dev\docs\

rem commit the changes

@git add .
@git commit -m"user guide"
