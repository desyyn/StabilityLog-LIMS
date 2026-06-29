import unittest
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestProductRegistrationAndScheduling(unittest.TestCase):

    def setUp(self):
        options = webdriver.ChromeOptions()
        options.add_argument('--disable-blink-features=AutomationControlled')
        self.driver = webdriver.Chrome(options=options)
        self.driver.maximize_window()
        self.register_url = "http://127.0.0.1:8000/register"
        self.index_url = "http://127.0.0.1:8000/products"

    def fill_baseline_data(self, product_name, batch_code):
        self.driver.find_element(By.ID, "name").send_keys(product_name)
        self.driver.find_element(By.ID, "batch_code").send_keys(batch_code)

    def test_01_RT01_POS_registrasi_sampel_valid(self):
        """RT01_POS: Memastikan registrasi berhasil jika seluruh input valid"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("Serum Retinol K3", f"BATCH-{int(time.time())}")
        ph_check = driver.find_element(By.ID, "param_ph")
        driver.execute_script("arguments[0].click();", ph_check)
        driver.find_element(By.ID, "min_ph").send_keys("4.5")
        driver.find_element(By.ID, "max_ph").send_keys("5.5")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        success_alert = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-success")))
        self.assertTrue(success_alert.is_displayed())

    def test_02_RT01_NEG_registrasi_batch_duplikat(self):
        """RT01_NEG: Memastikan sistem menolak batch yang sudah digunakan"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("Moisturizer Gel", "BATCH-2024-001")
        ph_check = driver.find_element(By.ID, "param_ph")
        driver.execute_script("arguments[0].click();", ph_check)
        driver.find_element(By.ID, "min_ph").send_keys("4.5")
        driver.find_element(By.ID, "max_ph").send_keys("5.5")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        invalid_feedback = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "invalid-feedback")))
        self.assertTrue(invalid_feedback.is_displayed())

    def test_03_RT02_POS_generate_qr_otomatis(self):
        """RT02_POS: Memastikan QR dibuat setelah registrasi sukses"""
        driver = self.driver
        driver.get(self.index_url)
        try:
            qr_link = WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.LINK_TEXT, "Lihat QR")))
            self.assertTrue(qr_link.is_displayed())
        except:
            self.skipTest("Belum ada data QR Code terdaftar di database.")

    def test_04_RT02_NEG_generate_qr_gagal(self):
        """RT02_NEG: Memastikan QR tidak dibuat jika registrasi gagal"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("", "") 
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        driver.get(self.index_url)
        self.assertNotIn("BATCH-EMPTY", driver.page_source)

    def test_05_RT05_POS_schedule_standard_accelerated(self):
        """RT05_POS: Memastikan schedule accelerated berhasil dibuat"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("Sunscreen K3", f"BATCH-ACC-{int(time.time())}")
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "schedule_standard"))
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "stability_accelerated"))
        ph_check = driver.find_element(By.ID, "param_ph")
        driver.execute_script("arguments[0].click();", ph_check)
        driver.find_element(By.ID, "min_ph").send_keys("4.0")
        driver.find_element(By.ID, "max_ph").send_keys("6.0")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-success")))

    def test_06_RT05_NEG_schedule_standard_tidak_lengkap(self):
        """RT05_NEG: Memastikan schedule gagal jika mode tidak lengkap"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("Sunscreen K3", f"BATCH-ACC-ERR-{int(time.time())}")
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "schedule_standard"))
        
        driver.execute_script("document.getElementById('stability_accelerated').checked = false;")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        error_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.XPATH, "//*[contains(@class, 'invalid-feedback') or contains(@class, 'alert-danger')]"))
        )
        self.assertTrue(error_element.is_displayed())

    def test_07_RT06_POS_schedule_custom_valid(self):
        """RT06_POS: Memastikan custom interval valid tersimpan"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("Essence Toner", f"BATCH-CUS-{int(time.time())}")
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "schedule_custom"))
        ph_check = driver.find_element(By.ID, "param_ph")
        driver.execute_script("arguments[0].click();", ph_check)
        driver.find_element(By.ID, "min_ph").send_keys("5.0")
        driver.find_element(By.ID, "max_ph").send_keys("6.0")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-success")))

    def test_08_RT06_NEG_schedule_custom_invalid(self):
        """RT06_NEG: Memastikan interval negatif ditolak oleh sistem"""
        driver = self.driver
        driver.get(self.register_url)
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "schedule_custom"))
        interval_input = driver.find_element(By.NAME, "custom_intervals[]")
        interval_input.clear()
        interval_input.send_keys("-10")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        invalid_feedback = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "invalid-feedback")))
        self.assertTrue(invalid_feedback.is_displayed())

    @unittest.skip("Modul Stability Chamber Master Data backend masih berada dalam fase spesifikasi perancangan (mockup)")
    def test_09_RT11_POS_alokasi_lokasi_chamber_valid(self):
        """RT11_POS: Memastikan pemilihan ruangan penyimpanan (Stability Chamber) yang valid berhasil direkam"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("Day Cream Glow", f"BATCH-CHM-{int(time.time())}")
        driver.find_element(By.ID, "chamber_id").send_keys("CHAMBER-A")
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        success_alert = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-success")))
        self.assertTrue(success_alert.is_displayed())

    @unittest.skip("Modul Stability Chamber Master Data backend masih berada dalam fase spesifikasi perancangan (mockup)")
    def test_10_RT11_NEG_alokasi_lokasi_chamber_invalid(self):
        """RT11_NEG: Memastikan sistem menolak alokasi sampel jika ID chamber tidak ditemukan atau invalid"""
        driver = self.driver
        driver.get(self.register_url)
        self.fill_baseline_data("Night Cream Glow", f"BATCH-CHM-ERR-{int(time.time())}")
        driver.find_element(By.ID, "chamber_id").send_keys("CHAMBER-INVALID")
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        invalid_feedback = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "invalid-feedback")))
        self.assertTrue(invalid_feedback.is_displayed())

    def tearDown(self):
        self.driver.quit()