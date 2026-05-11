#!/usr/bin/env python3
import sys
import json
from deepface import DeepFace

def main():
    if len(sys.argv) != 3:
        print(json.dumps({"success": False, "error": "Usage: compare.py <ic_path> <selfie_path>"}))
        sys.exit(1)

    ic_path     = sys.argv[1]
    selfie_path = sys.argv[2]

    try:
        # Verify face exists in selfie
        selfie_faces = DeepFace.extract_faces(img_path=selfie_path, enforce_detection=True)
        if not selfie_faces:
            print(json.dumps({"success": False, "error": "No face detected in selfie."}))
            sys.exit(0)

        # Verify face exists in IC
        ic_faces = DeepFace.extract_faces(img_path=ic_path, enforce_detection=True)
        if not ic_faces:
            print(json.dumps({"success": False, "error": "No face detected in IC photo."}))
            sys.exit(0)

        # Compare faces
        result = DeepFace.verify(
            img1_path   = ic_path,
            img2_path   = selfie_path,
            model_name  = "Facenet",
            enforce_detection = True,
            detector_backend  = "opencv",
        )

        # DeepFace returns distance — convert to similarity %
        # Facenet threshold is 10, max distance ~20
        distance   = result["distance"]
        threshold  = result["threshold"]
        similarity = max(0.0, (1 - distance / (threshold * 2))) * 100

        print(json.dumps({
            "success":    True,
            "verified":   result["verified"],
            "similarity": round(similarity, 2),
            "distance":   round(distance, 4),
            "threshold":  threshold,
        }))

    except ValueError as e:
        error_msg = str(e)
        if "Face could not be detected" in error_msg:
            if "img1" in error_msg:
                print(json.dumps({"success": False, "error": "No face detected in IC photo. Please upload a clear photo of your IC front side."}))
            else:
                print(json.dumps({"success": False, "error": "No face detected in selfie. Please take a clear, well-lit photo."}))
        else:
            print(json.dumps({"success": False, "error": error_msg}))
    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))

if __name__ == "__main__":
    main()