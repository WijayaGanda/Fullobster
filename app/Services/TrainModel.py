import pandas as pd
import numpy as np
from sklearn.tree import DecisionTreeClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix
import pickle
import os

def train_model():
    """
    Script untuk melatih model Decision Tree untuk klasifikasi kualitas air
    
    Pastikan Anda memiliki file dataset dengan format:
    pH, amonia, suhu, do, status (0=tidak perlu kuras, 1=perlu kuras)
    """
    
    print("=" * 60)
    print("TRAINING MODEL KLASIFIKASI KUALITAS AIR")
    print("=" * 60)
    
    # Path ke data training
    # Sesuaikan dengan lokasi file dataset Anda
    data_path = input("Masukkan path ke file CSV training data (kosongkan untuk dummy data): ").strip()
    
    if not data_path:
        print("\nMenggunakan dummy data untuk demonstrasi...")
        df = create_dummy_dataset()
    else:
        print(f"\nMemuat data dari {data_path}...")
        try:
            df = pd.read_csv(data_path)
        except Exception as e:
            print(f"Error membaca file: {e}")
            print("Menggunakan dummy data sebagai gantinya...")
            df = create_dummy_dataset()
    
    print(f"\nJumlah data: {len(df)}")
    print(f"Kolom: {df.columns.tolist()}")
    print(f"\nPreview data:")
    print(df.head())
    
    # Pisahkan features dan target
    X = df[['pH', 'amonia', 'suhu', 'do']]
    y = df['status']
    
    print(f"\nDistribusi kelas:")
    print(y.value_counts())
    
    # Split data
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    
    print(f"\nData training: {len(X_train)}")
    print(f"Data testing: {len(X_test)}")
    
    # Train model
    print("\nMemulai training...")
    model = DecisionTreeClassifier(
        max_depth=5,
        min_samples_split=5,
        min_samples_leaf=2,
        random_state=42
    )
    
    model.fit(X_train, y_train)
    print("Training selesai!")
    
    # Evaluasi
    print("\n" + "=" * 60)
    print("EVALUASI MODEL")
    print("=" * 60)
    
    y_pred = model.predict(X_test)
    accuracy = accuracy_score(y_test, y_pred)
    
    print(f"\nAkurasi: {accuracy:.2%}")
    print("\nClassification Report:")
    print(classification_report(y_test, y_pred, target_names=['Tidak Perlu Kuras', 'Perlu Kuras']))
    
    print("\nConfusion Matrix:")
    print(confusion_matrix(y_test, y_pred))
    
    # Feature importance
    print("\nFitur Penting:")
    feature_importance = pd.DataFrame({
        'feature': X.columns,
        'importance': model.feature_importances_
    }).sort_values('importance', ascending=False)
    print(feature_importance)
    
    # Save model
    print("\n" + "=" * 60)
    save = input("Simpan model? (y/n): ").lower()
    
    if save == 'y':
        # Tentukan path untuk menyimpan model
        script_dir = os.path.dirname(os.path.abspath(__file__))
        model_dir = os.path.join(script_dir, '..', '..', 'data', 'datatraining')
        model_dir = os.path.normpath(model_dir)
        
        # Buat direktori jika belum ada
        os.makedirs(model_dir, exist_ok=True)
        
        model_path = os.path.join(model_dir, 'model_decision_tree.pkl')
        
        with open(model_path, 'wb') as f:
            pickle.dump(model, f, protocol=pickle.HIGHEST_PROTOCOL)
        
        print(f"\nModel berhasil disimpan ke: {model_path}")
        print("\nCatatan: Model ini kompatibel dengan Python 3.x")
    else:
        print("\nModel tidak disimpan.")
    
    print("\n" + "=" * 60)
    print("SELESAI")
    print("=" * 60)

def create_dummy_dataset():
    """
    Membuat dummy dataset untuk demonstrasi
    """
    np.random.seed(42)
    
    # Generate data dengan kondisi berbeda
    n_samples = 200
    
    # Data kualitas baik (tidak perlu kuras)
    good_data = []
    for _ in range(n_samples // 2):
        good_data.append({
            'pH': np.random.uniform(6.5, 7.5),
            'amonia': np.random.uniform(0.01, 0.05),
            'suhu': np.random.uniform(23, 26),
            'do': np.random.uniform(3.5, 6),
            'status': 0
        })
    
    # Data kualitas buruk (perlu kuras)
    bad_data = []
    for _ in range(n_samples // 2):
        # Random pilih parameter yang buruk
        choice = np.random.randint(0, 4)
        
        if choice == 0:  # pH buruk
            bad_data.append({
                'pH': np.random.choice([np.random.uniform(5.5, 6.3), np.random.uniform(7.7, 8.5)]),
                'amonia': np.random.uniform(0.01, 0.08),
                'suhu': np.random.uniform(20, 28),
                'do': np.random.uniform(2.0, 6),
                'status': 1
            })
        elif choice == 1:  # Amonia tinggi
            bad_data.append({
                'pH': np.random.uniform(6.3, 7.7),
                'amonia': np.random.uniform(0.06, 0.15),
                'suhu': np.random.uniform(20, 28),
                'do': np.random.uniform(2.0, 6),
                'status': 1
            })
        elif choice == 2:  # Suhu ekstrem
            bad_data.append({
                'pH': np.random.uniform(6.3, 7.7),
                'amonia': np.random.uniform(0.01, 0.08),
                'suhu': np.random.choice([np.random.uniform(18, 21), np.random.uniform(28, 32)]),
                'do': np.random.uniform(2.0, 6),
                'status': 1
            })
        else:  # DO rendah
            bad_data.append({
                'pH': np.random.uniform(6.3, 7.7),
                'amonia': np.random.uniform(0.01, 0.08),
                'suhu': np.random.uniform(20, 28),
                'do': np.random.uniform(1.0, 2.5),
                'status': 1
            })
    
    # Gabungkan data
    all_data = good_data + bad_data
    df = pd.DataFrame(all_data)
    
    # Shuffle
    df = df.sample(frac=1).reset_index(drop=True)
    
    return df

if __name__ == '__main__':
    try:
        train_model()
    except KeyboardInterrupt:
        print("\n\nProgram dihentikan oleh user.")
    except Exception as e:
        print(f"\n\nError: {e}")
        import traceback
        traceback.print_exc()
