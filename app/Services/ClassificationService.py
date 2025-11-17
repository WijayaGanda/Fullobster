import sys
import json
import pickle
import numpy as np
import os
import warnings
warnings.filterwarnings('ignore')

def classify_water_quality(ph, amonia, suhu, do):
    """
    Klasifikasi kualitas air menggunakan model yang sudah ditraining
    """
    try:
        # Path ke model
        base_dir = os.path.dirname(os.path.abspath(__file__))
        model_path = os.path.join(base_dir, '..', '..', 'data', 'datatraining', 'model_decision_tree.pkl')
        model_path = os.path.normpath(model_path)
        
        # Cek apakah file model ada
        if not os.path.exists(model_path):
            return simple_classification(ph, amonia, suhu, do, error=f"Model file not found: {model_path}")
        
        # Load model dengan encoding yang sesuai
        try:
            # Try loading with different protocols
            with open(model_path, 'rb') as file:
                try:
                    # Try default first
                    model = pickle.load(file)
                except Exception as e1:
                    # Try with encoding
                    file.seek(0)
                    try:
                        model = pickle.load(file, encoding='latin1')
                    except Exception as e2:
                        # Try with fix_imports
                        file.seek(0)
                        try:
                            model = pickle.load(file, encoding='bytes', fix_imports=True)
                        except Exception as e3:
                            # Try joblib as alternative
                            file.seek(0)
                            try:
                                import joblib
                                model = joblib.load(model_path)
                            except Exception as e4:
                                return simple_classification(ph, amonia, suhu, do, 
                                    error=f"All loading methods failed. Last error: {str(e4)}")
        except Exception as e:
            # Jika ada error saat load model, gunakan klasifikasi sederhana
            return simple_classification(ph, amonia, suhu, do, error=f"Error loading model: {str(e)}")
        
        # Prepare data untuk prediksi
        # Format: [pH, amonia, suhu, do]
        input_data = np.array([[ph, amonia, suhu, do]])
        
        # Prediksi
        prediction = model.predict(input_data)[0]
        
        # Convert prediction ke format standar (0 atau 1)
        # Handle jika model return string atau integer
        if isinstance(prediction, str):
            # Map string labels ke integer
            # Asumsi: 'Layak' = 0 (tidak perlu kuras), 'Kurang Layak' = 1 (perlu kuras)
            label_map = {
                'Layak': 0,
                'LAYAK': 0,
                'layak': 0,
                'Tidak Perlu Kuras': 0,
                'TIDAK PERLU KURAS': 0,
                'tidak perlu kuras': 0,
                'Kurang Layak': 1,
                'KURANG LAYAK': 1,
                'kurang layak': 1,
                'Perlu Kuras': 1,
                'PERLU KURAS': 1,
                'perlu kuras': 1,
                'Kuras': 1,
                'KURAS': 1,
                'kuras': 1
            }
            prediction_int = label_map.get(prediction, 1)  # Default ke 1 (perlu kuras) jika tidak dikenali
        else:
            prediction_int = int(prediction)
        
        # Get probability jika tersedia
        try:
            probability = model.predict_proba(input_data)[0]
            confidence = float(max(probability) * 100)
        except:
            confidence = None
        
        result = {
            'classification': prediction_int,
            'confidence': confidence,
            'method': 'decision_tree_model',
            'original_prediction': str(prediction),
            'input': {
                'ph': float(ph),
                'amonia': float(amonia),
                'suhu': float(suhu),
                'do': float(do)
            }
        }
        
        return result
        
    except Exception as e:
        return simple_classification(ph, amonia, suhu, do, error=str(e))

def simple_classification(ph, amonia, suhu, do, error=None):
    """
    Klasifikasi sederhana berdasarkan threshold
    Digunakan sebagai fallback jika model tidak tersedia
    """
    # Range optimal untuk budidaya ikan:
    # pH: 6.5 - 7.5
    # Amonia: < 0.05 mg/L
    # Suhu: 23 - 26 °C
    # DO: > 3.5 mg/L
    
    needs_drain = False
    reasons = []

    if ph < 6.3 or ph > 7.7:
        needs_drain = True
        reasons.append(f'pH tidak optimal ({ph})')

    if amonia > 0.06:
        needs_drain = True
        reasons.append(f'Amonia tinggi ({amonia} mg/L)')

    if suhu < 21 or suhu > 28:
        needs_drain = True
        reasons.append(f'Suhu tidak ideal ({suhu}°C)')

    if do < 2.5:
        needs_drain = True
        reasons.append(f'Oksigen terlarut rendah ({do} mg/L)')

    result = {
        'classification': 1 if needs_drain else 0,
        'confidence': None,
        'method': 'simple_threshold',
        'reasons': reasons,
        'input': {
            'ph': float(ph),
            'amonia': float(amonia),
            'suhu': float(suhu),
            'do': float(do)
        }
    }
    
    if error:
        result['note'] = f'Using fallback classification. Model error: {error}'
    
    return result

if __name__ == '__main__':
    # Read input from command line arguments
    if len(sys.argv) == 5:
        try:
            ph = float(sys.argv[1])
            amonia = float(sys.argv[2])
            suhu = float(sys.argv[3])
            do = float(sys.argv[4])
            
            result = classify_water_quality(ph, amonia, suhu, do)
            print(json.dumps(result))
        except ValueError as e:
            print(json.dumps({'error': f'Invalid number format: {str(e)}', 'classification': 0}))
    else:
        print(json.dumps({
            'error': f'Invalid arguments. Expected 4 arguments (ph, amonia, suhu, do), got {len(sys.argv)-1}',
            'classification': 0
        }))
