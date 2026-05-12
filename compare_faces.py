#!/usr/bin/env python3
"""
compare_faces.py  <ic_path> <selfie_path>
Uses Face++ Compare API — accurate, no local model needed.
Register at https://www.faceplusplus.com to get API key and secret.
"""
import sys
import json
import os
import requests

# ── Face++ credentials ────────────────────────────────────────────────────────
API_KEY    = "MXQdmXQWViracrkEPoy5syfYpME9uNTK"
API_SECRET = "dL02pskRccdmXbtKJITnn1ruqMPxJb89"
API_URL    = "https://api-us.faceplusplus.com/facepp/v3/compare"

PASS_PCT   = 80.0  # minimum confidence % to pass


def compare_faces(ic_path: str, selfie_path: str) -> dict:
    try:
        with open(ic_path, "rb") as ic_file, open(selfie_path, "rb") as selfie_file:
            response = requests.post(
                API_URL,
                data={
                    "api_key":    API_KEY,
                    "api_secret": API_SECRET,
                },
                files={
                    "image_file1": ic_file,
                    "image_file2": selfie_file,
                },
                timeout=30
            )

        data = response.json()

        if "error_message" in data:
            error = data["error_message"]
            if "FACE_NOT_FOUND" in error:
                if "IMAGE_1" in error:
                    return {"success": False, "error": "No face detected in IC photo. Please upload a clear photo of your IC front side."}
                else:
                    return {"success": False, "error": "No face detected in selfie. Please face the camera directly in good lighting."}
            return {"success": False, "error": f"Face verification error: {error}"}

        confidence = data.get("confidence", 0)
        similarity = round(confidence, 1)
        verified   = similarity >= PASS_PCT

        return {
            "success":    True,
            "verified":   verified,
            "similarity": similarity,
            "distance":   round((100 - confidence) / 100, 4),
            "threshold":  PASS_PCT,
        }

    except requests.exceptions.Timeout:
        return {"success": False, "error": "Face verification timed out. Please try again."}
    except requests.exceptions.ConnectionError:
        return {"success": False, "error": "Cannot connect to face verification service. Check your internet connection."}
    except Exception as e:
        return {"success": False, "error": str(e)}


def main():
    if len(sys.argv) != 3:
        print(json.dumps({"success": False, "error": "Usage: compare_faces.py <ic_path> <selfie_path>"}))
        sys.exit(1)

    ic_path, selfie_path = sys.argv[1], sys.argv[2]

    if not os.path.exists(ic_path):
        print(json.dumps({"success": False, "error": f"IC file not found: {ic_path}"}))
        sys.exit(1)
    if not os.path.exists(selfie_path):
        print(json.dumps({"success": False, "error": f"Selfie file not found: {selfie_path}"}))
        sys.exit(1)

    result = compare_faces(ic_path, selfie_path)
    print(json.dumps(result))


if __name__ == "__main__":
    main()