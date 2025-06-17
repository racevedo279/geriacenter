# geriatric_management_system/database.py
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from geriatric_management_system.models import Base # Import Base from models
from config import DATABASE_URL

engine = create_engine(DATABASE_URL, connect_args={"check_same_thread": False}) # check_same_thread for SQLite
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

def init_db():
    # Create all tables in the database.
    # This is typically called once at application startup.
    Base.metadata.create_all(bind=engine)

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
