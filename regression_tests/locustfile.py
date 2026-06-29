import random
from locust import HttpUser, task, between

class StabilityLogLoadTest(HttpUser):
    wait_time = between(1, 2)

    @task
    def register_product(self):
        unique_id = random.randint(100000, 999999)

        payload = {
            "name": "Serum Retinol LocustTest",
            "batch_code": f"BATCH-LOCUST-{unique_id}",
            "schedule_mode": "standard",
            "stability_type": "accelerated",
            "parameters": {
                "ph": {"enabled": True,"param_name": "pH","type": "numeric","min_limit": 4.5,"max_limit": 5.5}
            }
        }

        headers = {
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        }

        with self.client.post(
            "/register",
            json=payload,
            headers=headers,
            catch_response=True
        ) as response:

            if response.status_code in [200, 201]:
                response.success()

            elif response.status_code == 422:
                print("\n[VALIDATION ERROR]")
                print(response.text)
                response.failure("Validation Failed")

            else:
                print(f"\n[HTTP {response.status_code}]")
                print(response.text)
                response.failure(f"Unexpected {response.status_code}")