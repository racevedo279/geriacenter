# tests/test_residents_crud.py
from datetime import date
from geriatric_management_system.crud_residents import (
    create_resident, get_resident, get_residents, update_resident, delete_resident
)
from geriatric_management_system.models.resident import Resident
from tests.base_test import BaseTestCase

class TestResidentsCRUD(BaseTestCase):

    def test_create_resident(self):
        resident_data = {
            "first_name": "Test", "last_name": "Resident",
            "date_of_birth": date(1950, 1, 1), "admission_date": date(2023, 1, 1),
            "room_number": "T101"
        }
        resident = create_resident(self.db, **resident_data)
        self.assertIsNotNone(resident.id)
        self.assertEqual(resident.first_name, "Test")

        db_resident = self.db.query(Resident).filter(Resident.id == resident.id).first()
        self.assertIsNotNone(db_resident)
        self.assertEqual(db_resident.last_name, "Resident")

    def test_get_resident(self):
        res1_data = {"first_name": "Alice", "last_name": "Smith", "date_of_birth": date(1960, 5, 5), "admission_date": date(2022, 3, 3)}
        res1 = create_resident(self.db, **res1_data)
        fetched_res = get_resident(self.db, res1.id)
        self.assertIsNotNone(fetched_res)
        self.assertEqual(fetched_res.id, res1.id)
        self.assertIsNone(get_resident(self.db, 99999))

    def test_get_residents(self):
        create_resident(self.db, first_name="Bob", last_name="Brown", date_of_birth=date(1955, 6, 6), admission_date=date(2021, 7, 7))
        create_resident(self.db, first_name="Charlie", last_name="Davis", date_of_birth=date(1949, 8, 8), admission_date=date(2023, 9, 9))
        all_residents = get_residents(self.db)
        # Each test runs with a fresh DB due to setUpClass/tearDownClass and rollback in tearDown
        # So, if these are the only two created in this test method, count should be 2.
        # However, if create_resident commits and tearDown doesn't clean perfectly, this might be flaky.
        # The current setup with rollback in tearDown and drop_all in tearDownClass should be fine.
        self.assertEqual(len(all_residents), 2)
        limited_residents = get_residents(self.db, limit=1)
        self.assertEqual(len(limited_residents), 1)


    def test_update_resident(self):
        resident_data = {"first_name": "Initial", "last_name": "Name", "date_of_birth": date(1930,1,1), "admission_date": date(2020,1,1), "room_number": "R1"}
        resident = create_resident(self.db, **resident_data)
        updated_resident = update_resident(self.db, resident.id, first_name="UpdatedFirst", room_number="R2")
        self.assertEqual(updated_resident.first_name, "UpdatedFirst")
        self.assertEqual(updated_resident.room_number, "R2")
        db_res = self.db.query(Resident).filter(Resident.id == resident.id).first()
        self.assertEqual(db_res.first_name, "UpdatedFirst")

    def test_delete_resident(self):
        resident_data = {"first_name": "ToDelete", "last_name": "Resident", "date_of_birth": date(1945,1,1), "admission_date": date(2022,1,1)}
        resident = create_resident(self.db, **resident_data)
        delete_resident(self.db, resident.id)
        self.assertIsNone(get_resident(self.db, resident.id))
