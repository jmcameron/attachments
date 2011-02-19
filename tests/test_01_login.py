from lib.JoomlaTestClient import *


def test_login():
    client = JoomlaTestClient(__name__)

    client.login('admin')
