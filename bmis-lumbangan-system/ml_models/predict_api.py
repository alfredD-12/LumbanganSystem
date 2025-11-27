# ml_models/predict_api.py
from fastapi import FastAPI
from pydantic import BaseModel
import joblib, numpy as np, pandas as pd

app = FastAPI()
MODEL_PATH = "nb_pipeline.joblib"
model = joblib.load(MODEL_PATH)

class Features(BaseModel):
    age: float
    household_size: int
    sex: str
    to_purok_id: int
    timeframe: str = 'month'  # unused in demo but for future extension

@app.post("/predict")
def predict(f: Features):
    df = pd.DataFrame([{
        'age': f.age,
        'household_size': f.household_size,
        'sex': 0 if f.sex=='M' else 1,
        'to_purok_id': f.to_purok_id
    }])
    # model expects column order X used during training
    prob = model.predict_proba(df)[0].tolist()
    pred = int(model.predict(df)[0])
    return {'prediction': pred, 'probabilities': prob}
