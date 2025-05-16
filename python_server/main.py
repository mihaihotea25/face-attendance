from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
import torch
import torchvision.transforms as transforms
from torchvision import models
from PIL import Image
import io
import os

# === Load your model ===
DEVICE = torch.device("cuda" if torch.cuda.is_available() else "cpu")
MODEL_PATH = 'face_recognition_model_best.pth'
DATA_DIR = 'C:/Licenta/small_dataset/dataset/data/train'
CLASS_NAMES = sorted(os.listdir(DATA_DIR))

model = models.resnet18(weights=None)
num_ftrs = model.fc.in_features
model.fc = torch.nn.Sequential(
    torch.nn.Dropout(0.4),
    torch.nn.Linear(num_ftrs, 50)  # adapt to your number of classes
)
model.load_state_dict(torch.load(MODEL_PATH, map_location=DEVICE))
model.to(DEVICE)
model.eval()


# === Setup FastAPI ===
app = FastAPI()

# Allow Laravel to call this API
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # 👈 later make this tighter (only your Laravel domain)
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

transform = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize([0.485, 0.456, 0.406],
                         [0.229, 0.224, 0.225])
])

@app.post("/predict")
async def predict(file: UploadFile = File(...)):
    image_bytes = await file.read()
    image = Image.open(io.BytesIO(image_bytes)).convert('RGB')
    input_tensor = transform(image).unsqueeze(0).to(DEVICE)

    with torch.no_grad():
        outputs = model(input_tensor)
        probabilities = torch.nn.functional.softmax(outputs, dim=1)
        predicted_index = torch.argmax(probabilities, dim=1)
        confidence_score = float(probabilities[0][predicted_index])

    student_id = CLASS_NAMES[predicted_index]

    return {
        "student": student_id,
        "confidence": confidence_score
    }