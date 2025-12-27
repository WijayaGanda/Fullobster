import sys
import json
import pickle
import numpy as np
import pandas as pd
import os
import warnings
warnings.filterwarnings('ignore')

def classify_water_quality(ph, tds, suhu, do, use_simple=False, debug=False):
    """
    Klasifikasi kualitas air menggunakan model yang sudah ditraining
    
    Parameters:
    - ph: pH air (6.5-7.8 layak)
    - tds: Total Dissolved Solids dalam mg/L (50-400 layak)
    - suhu: Suhu air dalam Celsius (23-25 layak)
    - do: Dissolved Oxygen dalam mg/L (4-6 layak)
    - use_simple: Force menggunakan simple classification (untuk testing)
    - debug: Print debug information
    """
    
    # Force simple classification jika diminta
    if use_simple:
        return simple_classification(ph, tds, suhu, do)
    
    try:
        # Path ke model
        base_dir = os.path.dirname(os.path.abspath(__file__))
        model_path = os.path.join(base_dir, '..', '..', 'data', 'datatraining', 'model_decision_tree_lobster.pkl')
        model_path = os.path.normpath(model_path)
        
        # Cek apakah file model ada
        if not os.path.exists(model_path):
            return simple_classification(ph, tds, suhu, do, error=f"Model file not found: {model_path}")
        
        # Load model dengan joblib
        try:
            import joblib
            model = joblib.load(model_path)
        except Exception as e:
            return simple_classification(ph, tds, suhu, do, error=f"Error loading model: {str(e)}")
        
        # Prepare data dengan pandas DataFrame
        # Urutan kolom sesuai training: suhu, ph, do, tds
        data = pd.DataFrame([{
            "suhu": suhu,
            "ph": ph,
            "do": do,
            "tds": tds
        }])
        
        # Prediksi
        prediction = model.predict(data)[0]
        
        # Model output string: "Kurang Layak", "Layak", "Tidak Layak"
        # Convert ke integer: 0 = Kurang Layak, 1 = Layak, 2 = Tidak Layak
        if isinstance(prediction, str):
            prediction_str = prediction.strip()
            label_map = {
                'Layak': 1,
                'Kurang Layak': 0,
                'Kurang layak': 0,
                'Tidak Layak': 2,
                'Tidak layak': 2
            }
            prediction_int = label_map.get(prediction_str, 1)  # Default ke Layak jika tidak dikenali
        else:
            # Jika output integer langsung
            prediction_int = int(prediction)
            prediction_str = {0: 'Kurang Layak', 1: 'Layak', 2: 'Tidak Layak'}.get(prediction_int, 'Unknown')
        
        # Get probability jika tersedia
        try:
            probability = model.predict_proba(input_data)[0]
            confidence = float(max(probability) * 100)
        except:
            confidence = None
        
        # Get label name
        label_names = {0: 'Kurang Layak', 1: 'Layak', 2: 'Tidak Layak'}
        
        result = {
            'classification': prediction_int,
            'classification_label': label_names.get(prediction_int, 'Unknown'),
            'confidence': confidence,
            'method': 'decision_tree_model',
            'original_prediction': str(prediction),
            'prediction_type': 'string' if isinstance(prediction, str) else 'integer',
            'input': {
                'ph': float(ph),
                'tds': float(tds),
                'suhu': float(suhu),
                'do': float(do)
            }
        }
        
        return result
        
    except Exception as e:
        return simple_classification(ph, tds, suhu, do, error=str(e))

def simple_classification(ph, tds, suhu, do, error=None):
    """
    Klasifikasi sederhana berdasarkan threshold dengan 3 kategori
    Digunakan sebagai fallback jika model tidak tersedia
    
    Label: 0 = Kurang Layak, 1 = Layak, 2 = Tidak Layak
    
    Range Layak:
    - Suhu: 23-25
    - pH: 6.5-7.8
    - DO: 4-6
    - TDS: 50-400
    
    Range Kurang Layak (warning zone):
    - Suhu: 21-22 atau 26-27
    - pH: 6.0-6.4 atau 7.9-8.5
    - DO: 2.5-3.9 atau 6.1-7
    - TDS: 400-600 atau < 50
    
    Range Tidak Layak (perlu kuras):
    - Suhu: < 21 atau > 27
    - pH: < 6.0 atau > 8.5
    - DO: < 2.5 atau > 7
    - TDS: > 600
    """
    
    classification = 1  # Default: Layak
    reasons = []
    not_suitable_count = 0
    less_suitable_count = 0

    # Cek pH
    if ph < 6.0 or ph > 8.5:
        not_suitable_count += 1
        reasons.append(f'pH tidak layak ({ph}) - Range layak: 6.5-7.8')
    elif (6.0 <= ph < 6.5) or (7.8 < ph <= 8.5):
        less_suitable_count += 1
        reasons.append(f'pH kurang layak ({ph}) - Range layak: 6.5-7.8')

    # Cek TDS
    if tds > 600:
        not_suitable_count += 1
        reasons.append(f'TDS tidak layak ({tds} mg/L) - Range layak: 50-400 mg/L')
    elif (tds < 50) or (400 < tds <= 600):
        less_suitable_count += 1
        reasons.append(f'TDS kurang layak ({tds} mg/L) - Range layak: 50-400 mg/L')

    # Cek Suhu
    if suhu < 21 or suhu > 27:
        not_suitable_count += 1
        reasons.append(f'Suhu tidak layak ({suhu}°C) - Range layak: 23-25°C')
    elif (21 <= suhu < 23) or (25 < suhu <= 27):
        less_suitable_count += 1
        reasons.append(f'Suhu kurang layak ({suhu}°C) - Range layak: 23-25°C')

    # Cek DO
    if do < 2.5 or do > 7:
        not_suitable_count += 1
        reasons.append(f'DO tidak layak ({do} mg/L) - Range layak: 4-6 mg/L')
    elif (2.5 <= do < 4) or (6 < do <= 7):
        less_suitable_count += 1
        reasons.append(f'DO kurang layak ({do} mg/L) - Range layak: 4-6 mg/L')
    
    # Tentukan klasifikasi berdasarkan jumlah parameter
    # Prioritas: Jika ada 1 parameter tidak layak -> Tidak Layak
    if not_suitable_count > 0:
        classification = 2  # Tidak Layak - perlu kuras
    elif less_suitable_count > 0:
        classification = 0  # Kurang Layak - monitoring rutin
    else:
        classification = 1  # Layak

    # Get label name
    label_names = {0: 'Kurang Layak', 1: 'Layak', 2: 'Tidak Layak'}
    
    result = {
        'classification': classification,
        'classification_label': label_names.get(classification, 'Unknown'),
        'confidence': None,
        'method': 'simple_threshold',
        'reasons': reasons,
        'not_suitable_count': not_suitable_count,
        'less_suitable_count': less_suitable_count,
        'input': {
            'ph': float(ph),
            'tds': float(tds),
            'suhu': float(suhu),
            'do': float(do)
        }
    }
    
    if error:
        result['note'] = f'Using fallback classification. Model error: {error}'
    
    return result

if __name__ == '__main__':
    # Read input from command line arguments
    # Expected: python ClassificationService.py ph tds suhu do
    if len(sys.argv) == 5:
        try:
            ph = float(sys.argv[1])
            tds = float(sys.argv[2])
            suhu = float(sys.argv[3])
            do = float(sys.argv[4])
            
            result = classify_water_quality(ph, tds, suhu, do)
            print(json.dumps(result))
        except ValueError as e:
            print(json.dumps({'error': f'Invalid number format: {str(e)}', 'classification': 0}))
    else:
        print(json.dumps({
            'error': f'Invalid arguments. Expected 4 arguments (ph, tds, suhu, do), got {len(sys.argv)-1}',
            'classification': 0
        }))
