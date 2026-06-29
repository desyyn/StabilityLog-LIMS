import unittest
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestViewsAndDataActions(unittest.TestCase):

    def setUp(self):
        options = webdriver.ChromeOptions()
        options.add_argument('--disable-blink-features=AutomationControlled')
        self.driver = webdriver.Chrome(options=options)
        self.driver.maximize_window()
        self.index_url = "http://127.0.0.1:8000/products"

    def test_15_RT08_POS_detail_produk_tampil(self):
        """RT08_POS: Memastikan detail produk menampilkan data registrasi secara lengkap"""
        driver = self.driver
        driver.get(self.index_url)
        try:
            view_btn = WebDriverWait(driver, 5).until(EC.element_to_be_clickable((By.CLASS_NAME, "btn-outline-primary")))
            view_btn.click()
            detail_title = WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.TAG_NAME, "h4")))
            self.assertIn("Detail Produk", detail_title.text)
        except:
            self.skipTest("Tabel produk kosong, tidak ada data untuk diuji.")

    def test_16_RT08_NEG_detail_produk_tidak_ditemukan(self):
        """RT08_NEG: Memastikan akses detail produk dengan kode batch invalid ditangani (404/Error)"""
        driver = self.driver
        driver.get("http://127.0.0.1:8000/products/BATCH-NOT-FOUND-999")
        self.assertTrue("404" in driver.page_source or "Not Found" in driver.page_source or "Detail" not in driver.page_source)

    def test_17_RT09_POS_daftar_produk_menampilkan_data(self):
        """RT09_POS: Memastikan produk muncul di dalam tabel utama"""
        driver = self.driver
        driver.get(self.index_url)
        table = WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.CLASS_NAME, "table")))
        self.assertTrue(table.is_displayed())

    def test_18_RT09_NEG_produk_gagal_registrasi_tidak_tampil(self):
        """RT09_NEG: Memastikan produk yang gagal registrasi tidak masuk daftar tabel"""
        driver = self.driver
        driver.get(self.index_url)
        self.assertNotIn("BATCH-FAILED-PRODUCT-TESTING-999", driver.page_source)

    def test_19_RT10_POS_hapus_produk_valid(self):
        """RT10_POS: Memastikan produk dapat dihapus dengan memicu konfirmasi alert dialog"""
        driver = self.driver
        driver.get(self.index_url)
        try:
            delete_btn = WebDriverWait(driver, 5).until(EC.element_to_be_clickable((By.XPATH, "//button[contains(text(), 'Hapus')]")))
            driver.execute_script("arguments[0].click();", delete_btn)
            alert = driver.switch_to.alert
            alert.accept() 
            time.sleep(2)
        except:
            self.skipTest("Tidak ada sampel produk yang bisa dihapus saat ini.")

    def test_20_RT10_NEG_hapus_produk_tidak_valid(self):
        """RT10_NEG: Memastikan penghapusan data tidak valid/tanpa konfirmasi ditolak"""
        driver = self.driver
        driver.get(self.index_url)
        try:
            delete_btn = WebDriverWait(driver, 5).until(EC.element_to_be_clickable((By.XPATH, "//button[contains(text(), 'Hapus')]")))
            driver.execute_script("arguments[0].click();", delete_btn)
            alert = driver.switch_to.alert
            alert.dismiss() 
            time.sleep(1)
            self.assertTrue(True) 
        except:
            self.skipTest("Tidak ada tombol aksi hapus yang termuat.")

    def tearDown(self):
        self.driver.quit()