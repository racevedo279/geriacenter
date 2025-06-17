# tests/base_test.py
import unittest
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, Session
from geriatric_management_system.models import Base
from geriatric_management_system.database import get_db

TEST_DATABASE_URL = "sqlite:///:memory:"

engine = create_engine(TEST_DATABASE_URL, connect_args={"check_same_thread": False})
TestingSessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

def override_get_db():
    try:
        db = TestingSessionLocal()
        yield db
    finally:
        db.close()

class BaseTestCase(unittest.TestCase):
    db: Session

    @classmethod
    def setUpClass(cls):
        Base.metadata.create_all(bind=engine)

    def setUp(self):
        self.db = TestingSessionLocal()
        # Example of how you might override get_db if your CRUD functions use it directly
        # self.original_get_db = some_module.get_db
        # some_module.get_db = override_get_db

    def tearDown(self):
        self.db.rollback()
        self.db.close()
        # Example of restoring
        # some_module.get_db = self.original_get_db

    @classmethod
    def tearDownClass(cls):
        Base.metadata.drop_all(bind=engine)
