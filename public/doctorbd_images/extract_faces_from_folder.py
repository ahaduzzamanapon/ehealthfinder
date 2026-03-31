import cv2
import os
from pathlib import Path

# Get the current directory
current_dir = os.getcwd()

# Load the pre-trained face cascade classifier
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

# Image extensions to process
image_extensions = ['.jpg', '.jpeg', '.png', '.bmp', '.tiff']

# Find all images in current directory
image_files = []
for ext in image_extensions:
    image_files.extend(Path(current_dir).glob(f'*{ext}'))
    image_files.extend(Path(current_dir).glob(f'*{ext.upper()}'))

print(f"Found {len(image_files)} image(s) to process\n")

for image_path in image_files:
    print(f"Processing: {image_path.name}")
    
    # Read the image
    image = cv2.imread(str(image_path))
    
    if image is None:
        print(f"  Error: Could not read image\n")
        continue
    
    # Convert to grayscale for face detection
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    
    # Detect faces
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(30, 30))
    
    if len(faces) == 0:
        print(f"  No faces found - skipping\n")
        continue
    
    print(f"  Found {len(faces)} face(s)")
    
    # Extract the first (largest) face
    (x, y, w, h) = faces[0]
    
    # Add padding around face for a bigger, fuller crop
    padding = int(0.3 * max(w, h))  # 30% padding
    
    # Calculate new coordinates with padding
    x1 = max(0, x - padding)
    y1 = max(0, y - padding)
    x2 = min(image.shape[1], x + w + padding)
    y2 = min(image.shape[0], y + h + padding)
    
    # Extract face region with padding
    face_roi = image[y1:y2, x1:x2]
    
    # Delete the original image and save the face with the same name
    os.remove(str(image_path))
    cv2.imwrite(str(image_path), face_roi)
    print(f"  Replaced with face-only image (Size: {face_roi.shape[1]}x{face_roi.shape[0]} pixels)\n")

print("✓ All images processed successfully!")
