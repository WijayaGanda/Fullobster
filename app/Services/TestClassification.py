import sys
import os

# Tambahkan parent directory ke path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from ClassificationService import classify_water_quality, simple_classification

def test_classification():
    """
    Test klasifikasi dengan beberapa contoh data
    """
    print("=" * 70)
    print("TEST KLASIFIKASI KUALITAS AIR")
    print("=" * 70)
    
    # Test cases dengan berbagai kondisi
    test_cases = [
        {
            'name': 'Kondisi Optimal',
            'ph': 7.0,
            'amonia': 0.03,
            'suhu': 24.5,
            'do': 4.5
        },
        {
            'name': 'pH Tinggi',
            'ph': 7.9,
            'amonia': 0.04,
            'suhu': 24.0,
            'do': 4.0
        },
        {
            'name': 'Amonia Tinggi',
            'ph': 7.0,
            'amonia': 0.08,
            'suhu': 24.0,
            'do': 4.0
        },
        {
            'name': 'Suhu Rendah',
            'ph': 7.0,
            'amonia': 0.03,
            'suhu': 20.0,
            'do': 4.0
        },
        {
            'name': 'DO Rendah',
            'ph': 7.0,
            'amonia': 0.03,
            'suhu': 24.0,
            'do': 2.0
        },
        {
            'name': 'Kondisi Buruk (Multiple)',
            'ph': 6.0,
            'amonia': 0.07,
            'suhu': 29.0,
            'do': 2.2
        }
    ]
    
    for i, test in enumerate(test_cases, 1):
        print(f"\n{i}. {test['name']}")
        print("-" * 70)
        print(f"   pH: {test['ph']}")
        print(f"   Amonia: {test['amonia']} mg/L")
        print(f"   Suhu: {test['suhu']}°C")
        print(f"   DO: {test['do']} mg/L")
        
        result = classify_water_quality(
            test['ph'],
            test['amonia'],
            test['suhu'],
            test['do']
        )
        
        print(f"\n   Hasil: {'🔴 PERLU DIKURAS' if result['classification'] == 1 else '🟢 TIDAK PERLU DIKURAS'}")
        print(f"   Metode: {result['method']}")
        
        if result.get('confidence'):
            print(f"   Confidence: {result['confidence']:.1f}%")
        
        if result.get('reasons'):
            print(f"   Alasan:")
            for reason in result['reasons']:
                print(f"      - {reason}")
        
        if result.get('note'):
            print(f"   Catatan: {result['note']}")
    
    print("\n" + "=" * 70)
    print("TEST SELESAI")
    print("=" * 70)

if __name__ == '__main__':
    test_classification()
