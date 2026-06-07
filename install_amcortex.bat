@echo off

echo ==========================================
echo Installation automatique AMCortex
echo ==========================================

echo.
echo Installation WSL...
wsl --install

echo.
echo Mise a jour Ubuntu...
wsl sudo apt update

echo.
echo Installation Auto-Multiple-Choice...
wsl sudo apt install auto-multiple-choice -y

echo.
echo Installation XeLaTeX...
wsl sudo apt install texlive-xetex -y

echo.
echo Installation support arabe...
wsl sudo apt install texlive-lang-arabic -y

echo.
echo Installation Poppler...
wsl sudo apt install poppler-utils -y

echo.
echo Installation SQLite...
wsl sudo apt install sqlite3 -y

echo.
echo Installation ZIP...
wsl sudo apt install zip unzip -y

echo.
echo Installation Python packages...
pip install sentence-transformers
pip install datasets
pip install pandas
pip install torch

echo.
echo ==========================================
echo Installation terminee.
echo Redemarrez votre PC si necessaire.
echo ==========================================

pause