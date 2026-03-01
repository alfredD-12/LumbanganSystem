# ml_models/train_nb.py
import argparse, joblib, pandas as pd, numpy as np
from sqlalchemy import create_engine
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.compose import ColumnTransformer
from sklearn.pipeline import Pipeline
from sklearn.naive_bayes import GaussianNB
from sklearn.metrics import classification_report

def load_data_from_db(use_synthetic=True, use_real=True, db_conf=None):
    if db_conf is None:
        db_conf = {'host':'localhost','user':'root','password':'','database':'lumbangansystem'}
    
    # Create SQLAlchemy engine to avoid pandas warning
    engine = create_engine(
        f"mysql+mysqlconnector://{db_conf['user']}:{db_conf['password']}@{db_conf['host']}/{db_conf['database']}"
    )
    
    # Query base view with is_synthetic column
    df = pd.read_sql("SELECT * FROM ml_migration_dataset_all", engine)
    engine.dispose()  # Close connection
    if not use_synthetic:
        df = df[df['is_synthetic'] == 0]
    if not use_real:
        df = df[df['is_synthetic'] == 1]
    return df

def prepare_features(df):
    # basic features; add more as needed
    df = df.dropna(subset=['age', 'household_size'])
    X = df[['age', 'household_size', 'sex', 'to_purok_id']].copy()
    # encode sex -> numeric; keep OneHotEncoder for purok if desired
    X['sex'] = X['sex'].map({'M':0, 'F':1}).fillna(0)
    # example target: if this migration row is move-out event for the person in next month?
    # For synthetic demo we will create a label randomly or based on rule if not present
    if 'moved_out_next_month' in df.columns:
        y = df['moved_out_next_month']
    else:
        # Create realistic labels: Check if they actually moved to a different purok
        # If from_purok_id != to_purok_id, they migrated (label=1)
        # Otherwise, use age-based probabilities
        y = []
        for idx, row in df.iterrows():
            age = row['age']
            from_purok = row.get('from_purok_id')
            to_purok = row.get('to_purok_id')
            
            # If they moved to a different purok, label = 1 (migrated)
            if pd.notna(from_purok) and pd.notna(to_purok) and from_purok != to_purok:
                label = 1
            else:
                # Use age-based probability: younger people more likely to migrate
                # Age 18-25: 45% chance, Age 26-35: 40%, Age 36-50: 25%, Age 50+: 10%
                if age < 25:
                    prob = 0.45
                elif age < 35:
                    prob = 0.40
                elif age < 50:
                    prob = 0.25
                else:
                    prob = 0.10
                label = 1 if np.random.rand() < prob else 0
            y.append(label)
        
        y = np.array(y)
        print(f"Label distribution: Class 0 (stay): {sum(y==0)}, Class 1 (migrate): {sum(y==1)}")
    return X, y

def build_and_train(X, y):
    numeric_features = ['age','household_size']
    categorical_features = ['to_purok_id']  # handled via OneHot
    preprocessor = ColumnTransformer([
        ('num', StandardScaler(), numeric_features),
        ('cat', OneHotEncoder(handle_unknown='ignore', sparse_output=False), categorical_features)
    ])
    pipeline = Pipeline([
        ('pre', preprocessor),
        ('clf', GaussianNB())
    ])
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y if len(set(y))>1 else None)
    pipeline.fit(X_train, y_train)
    preds = pipeline.predict(X_test)
    print(classification_report(y_test, preds))
    return pipeline

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument('--mode', choices=['synthetic','real','both'], default='both', help='training data mode')
    args = parser.parse_args()

    use_synthetic = args.mode in ('synthetic','both')
    use_real = args.mode in ('real','both')

    df = load_data_from_db(use_synthetic=use_synthetic, use_real=use_real)
    print("Loaded rows:", len(df))
    if len(df) < 10:
        print("Not enough data to train (need at least 10 rows). Aborting.")
        exit(1)

    X, y = prepare_features(df)
    model = build_and_train(X, y)
    joblib.dump(model, 'nb_pipeline.joblib')
    print("Saved nb_pipeline.joblib")
