import unittest
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestParameterRulesAndBoundaries(unittest.TestCase):

    def setUp(self):
        options = webdriver.ChromeOptions()
        options.add_argument('--disable-blink-features=AutomationControlled')
        self.driver = webdriver.Chrome(options=options)
        self.driver.maximize_window()
        self.register_url = "http://127.0.0.1:8000/register"

    def test_09_RT03_POS_parameter_numerik_valid(self):
        """RT03_POS: Memastikan parameter numerik menerima min/max dan berhasil disimpan"""
        driver = self.driver
        driver.get(self.register_url)
        driver.find_element(By.ID, "name").send_keys("Serum Niacinamide")
        driver.find_element(By.ID, "batch_code").send_keys(f"BATCH-NUM-POS-{int(time.time())}")
        ph_check = driver.find_element(By.ID, "param_ph")
        driver.execute_script("arguments[0].click();", ph_check)
        driver.find_element(By.ID, "min_ph").send_keys("5.5")
        driver.find_element(By.ID, "max_ph").send_keys("6.5")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-success")))

    def test_10_RT03_NEG_parameter_numerik_tanpa_batas(self):
        """RT03_NEG: Memastikan parameter numerik wajib mengisi batas min/max"""
        driver = self.driver
        driver.get(self.register_url)
        driver.find_element(By.ID, "name").send_keys("Facial Wash K3")
        driver.find_element(By.ID, "batch_code").send_keys(f"BATCH-NUM-NEG-{int(time.time())}")
        ph_check = driver.find_element(By.ID, "param_ph")
        driver.execute_script("arguments[0].click();", ph_check)
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        invalid_feedback = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "invalid-feedback")))
        self.assertTrue(invalid_feedback.is_displayed())

    def test_11_RT04_POS_parameter_organoleptik_valid(self):
        driver = self.driver
        driver.get(self.register_url)
        driver.find_element(By.ID, "name").send_keys("Clay Mask K3")
        driver.find_element(By.ID, "batch_code").send_keys(f"BATCH-ORG-{int(time.time())}")
        color = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.ID, "param_color")))
        driver.execute_script("arguments[0].scrollIntoView({block:'center'});", color)
        driver.execute_script("arguments[0].click();", color)
        note = WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.ID, "organoleptic_color")))
        note.send_keys("warna stabil")
        submit = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].scrollIntoView({block:'center'});", submit)
        driver.execute_script("arguments[0].click();", submit)
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-success")))

    def test_12_RT04_NEG_parameter_organoleptik_salah_input(self):
        driver = self.driver
        driver.get(self.register_url)
        driver.execute_script("arguments[0].click();", driver.find_element(By.ID, "param_color"))
        min_input = driver.find_elements(By.ID, "min_color")
        if min_input:
            self.assertFalse(min_input[0].is_enabled())
        else:
            self.assertTrue(True)

    def test_13_RT07_POS_parameter_minimum_satu_dipilih(self):
        """RT07_POS: Memastikan sistem menerima jika minimal satu parameter dipilih"""
        driver = self.driver
        driver.get(self.register_url)
        driver.find_element(By.ID, "name").send_keys("Serum Eye C")
        driver.find_element(By.ID, "batch_code").send_keys(f"BATCH-ONEPARAM-{int(time.time())}")
        ph_check = driver.find_element(By.ID, "param_ph")
        driver.execute_script("arguments[0].click();", ph_check)
        driver.find_element(By.ID, "min_ph").send_keys("5.0")
        driver.find_element(By.ID, "max_ph").send_keys("6.0")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-success")))

    def test_14_RT07_NEG_tidak_memilih_parameter(self):
        """RT07_NEG: Memastikan sistem menolak jika tidak ada parameter"""
        driver = self.driver
        driver.get(self.register_url)
        driver.find_element(By.ID, "name").send_keys("Night Cream K3")
        driver.find_element(By.ID, "batch_code").send_keys(f"BATCH-NOPARAM-{int(time.time())}")
        
        submit_btn = driver.find_element(By.XPATH, "//button[@type='submit']")
        driver.execute_script("arguments[0].click();", submit_btn)
        
        error_alert = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CLASS_NAME, "alert-danger")))
        self.assertTrue(error_alert.is_displayed())

    def tearDown(self):
        self.driver.quit()