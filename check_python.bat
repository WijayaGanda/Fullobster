@echo off
echo Mengecek instalasi Python dan dependencies...
echo.

python --version
if %errorlevel% neq 0 (
    echo ERROR: Python tidak ditemukan!
    echo Silakan install Python dari https://www.python.org/downloads/
    pause
    exit /b 1
)

echo.
echo Mengecek package numpy...
python -c "import numpy; print('numpy version:', numpy.__version__)"
if %errorlevel% neq 0 (
    echo numpy tidak ditemukan. Installing...
    pip install numpy
)

echo.
echo Mengecek package scikit-learn...
python -c "import sklearn; print('scikit-learn version:', sklearn.__version__)"
if %errorlevel% neq 0 (
    echo scikit-learn tidak ditemukan. Installing...
    pip install scikit-learn
)

echo.
echo ========================================
echo Semua dependencies sudah siap!
echo ========================================
echo.
pause
