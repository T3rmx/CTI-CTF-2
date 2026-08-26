#!/usr/bin/env python3
"""
T3rmx CTF Flag Verifier
Validates captured flags against HMAC verification values.
Never exposes actual flag values.
"""

import hashlib
import hmac
import json
import sys
import os

def load_verification_data():
    """Load HMAC verification data from flags.json"""
    paths = [
        '/opt/t3rmx/verifier/flags.json',
        '/var/www/app/flags/flags.json',
        os.path.join(os.path.dirname(__file__), 'flags.json')
    ]
    for path in paths:
        if os.path.exists(path):
            with open(path, 'r') as f:
                return json.load(f)
    return None

def verify_flag(captured_flag, expected_hmac, secret):
    """Verify a captured flag against its HMAC"""
    computed = hmac.new(
        secret.encode(),
        captured_flag.encode(),
        hashlib.sha256
    ).hexdigest()
    return hmac.compare_digest(computed, expected_hmac)

def main():
    if len(sys.argv) < 2:
        print("Usage: verifier.py <flag> [flag2] [flag3]")
        print("Verifies captured flags against HMAC verification values.")
        print("Never exposes actual flag values.")
        sys.exit(1)

    data = load_verification_data()
    if not data:
        print("Error: Could not load verification data")
        sys.exit(1)

    secret = data['hmac_secret']
    flags_to_check = sys.argv[1:]

    results = {}
    flag_types = ['developer', 'laour', 'root']

    for flag in flags_to_check:
        found = False
        for flag_type in flag_types:
            if flag_type in data:
                if verify_flag(flag, data[flag_type], secret):
                    results[flag] = {'valid': True, 'type': flag_type}
                    found = True
                    break
        if not found:
            results[flag] = {'valid': False, 'type': None}

    # Output results
    print("=" * 50)
    print("T3rmx CTF - Flag Verification Results")
    print("=" * 50)

    all_valid = True
    for flag, result in results.items():
        status = "VALID" if result['valid'] else "INVALID"
        flag_type = result['type'] or "unknown"
        print(f"  [{status}] ({flag_type}) {flag[:20]}...")
        if not result['valid']:
            all_valid = False

    print("=" * 50)
    if all_valid:
        print("All flags verified successfully!")
    else:
        print("Some flags could not be verified.")
        sys.exit(1)

if __name__ == '__main__':
    main()
