import os
import sys
from pathlib import Path

def find_pdf_files(directory):
    """Busca archivos PDF en un directorio"""
    pdf_files = []
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.lower().endswith('.pdf'):
                pdf_files.append(os.path.join(root, file))
    return pdf_files

def extract_text_from_pdf(pdf_path):
    """Extrae texto de un archivo PDF usando PyPDF2"""
    try:
        # Importar dentro de la función para evitar errores si no está instalado
        import PyPDF2
    except ImportError:
        print("PyPDF2 no está instalado. Instalando...")
        try:
            import subprocess
            subprocess.check_call([sys.executable, "-m", "pip", "install", "PyPDF2"])
            import PyPDF2
        except:
            print("No se pudo instalar PyPDF2")
            return None

    try:
        with open(pdf_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            text = ""
            for page in reader.pages:
                text += page.extract_text() + "\n"
            return text
    except Exception as e:
        print(f"Error al leer el PDF: {e}")
        return None

def extract_text_from_pdf_alternative(pdf_path):
    """Extrae texto de un archivo PDF usando pdfplumber como alternativa"""
    try:
        import pdfplumber
    except ImportError:
        print("pdfplumber no está instalado. Instalando...")
        try:
            import subprocess
            subprocess.check_call([sys.executable, "-m", "pip", "install", "pdfplumber"])
            import pdfplumber
        except:
            print("No se pudo instalar pdfplumber")
            return None

    try:
        text = ""
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                page_text = page.extract_text()
                if page_text:
                    text += page_text + "\n"
        return text
    except Exception as e:
        print(f"Error al leer el PDF con pdfplumber: {e}")
        return None

def save_extracted_text(text, output_path):
    """Guarda el texto extraído en un archivo de texto"""
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Texto extraído guardado en: {output_path}")

def main():
    # Directorio donde buscar el PDF
    search_path = Path(__file__).parent.parent / "assets"
    print(f"Buscando archivos PDF en: {search_path}")
    
    pdf_files = find_pdf_files(search_path)
    
    if not pdf_files:
        print("No se encontraron archivos PDF en la carpeta assets")
        return
    
    print(f"Archivos PDF encontrados: {pdf_files}")
    
    for pdf_file in pdf_files:
        print(f"\nProcesando: {pdf_file}")
        
        # Intentar con PyPDF2 primero
        print("Intentando con PyPDF2...")
        text = extract_text_from_pdf(pdf_file)
        
        if text is None or text.strip() == "":
            print("PyPDF2 no funcionó, intentando con pdfplumber...")
            text = extract_text_from_pdf_alternative(pdf_file)
        
        if text and text.strip() != "":
            print("Texto extraído exitosamente")
            
            # Crear nombre de archivo de salida
            output_file = pdf_file.replace('.pdf', '_extracted.txt')
            save_extracted_text(text, output_file)
            
            print("\nPrimeras 500 palabras del documento:")
            print(text[:1000] + "..." if len(text) > 1000 else text)
        else:
            print("No se pudo extraer texto del PDF")

if __name__ == "__main__":
    main()