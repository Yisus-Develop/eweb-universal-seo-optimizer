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
        import PyPDF2
    except ImportError:
        print("PyPDF2 no está instalado.")
        return None

    try:
        with open(pdf_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            text = ""
            for page in reader.pages:
                try:
                    page_text = page.extract_text()
                    text += page_text + "\n---PAGE BREAK---\n"
                except:
                    print(f"Error al extraer texto de la página {reader.pages.index(page) + 1}")
                    continue
            return text
    except Exception as e:
        print(f"Error al leer el PDF: {e}")
        return None

def extract_text_from_pdf_alternative(pdf_path):
    """Extrae texto de un archivo PDF usando pdfplumber como alternativa"""
    try:
        import pdfplumber
    except ImportError:
        print("pdfplumber no está instalado.")
        return None

    try:
        text = ""
        with pdfplumber.open(pdf_path) as pdf:
            for i, page in enumerate(pdf.pages):
                try:
                    page_text = page.extract_text()
                    if page_text:
                        text += f"PAGE {i+1}:\n" + page_text + "\n---PAGE BREAK---\n"
                except:
                    print(f"Error al extraer texto de la página {i+1}")
                    continue
        return text
    except Exception as e:
        print(f"Error al leer el PDF con pdfplumber: {e}")
        return None

def save_extracted_text(text, output_path):
    """Guarda el texto extraído en un archivo de texto"""
    try:
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write(text)
        print(f"Texto extraído guardado en: {output_path}")
    except:
        # Si hay problemas de codificación, intentar con codificación 'latin-1'
        with open(output_path, 'w', encoding='latin-1') as f:
            f.write(text)
        print(f"Texto extraído guardado en: {output_path} (con codificación latin-1)")

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
        
        # Intentar con pdfplumber primero (generalmente más robusto)
        print("Intentando con pdfplumber...")
        text = extract_text_from_pdf_alternative(pdf_file)
        
        if text is None or text.strip() == "":
            print("pdfplumber no funcionó, intentando con PyPDF2...")
            text = extract_text_from_pdf(pdf_file)
        
        if text and text.strip() != "":
            print("Texto extraído exitosamente")
            
            # Crear nombre de archivo de salida
            output_file = pdf_file.replace('.pdf', '_extracted.txt')
            save_extracted_text(text, output_file)
            
            # Mostrar primeras 2000 caracteres del documento
            preview_length = min(2000, len(text))
            print(f"\nPrimeras {preview_length} caracteres del documento:")
            print(text[:preview_length])
            
            # Contar páginas
            page_count = text.count("---PAGE BREAK---")
            print(f"\nNúmero total de páginas procesadas: {page_count}")
        else:
            print("No se pudo extraer texto del PDF")

if __name__ == "__main__":
    main()