from windmill.authoring import WindmillTestClient

import functest

class JoomlaTestClient(WindmillTestClient):

    def __init__(self, name, home=u'http://localhost/test/joomla'):
        WindmillTestClient.__init__(self, name)
        self._home = home

    def login(self, username=None, password=None):
        if not username:
            username = functest.registry['username']
        if not password:
            password = functest.registry['password']
        self.click(id=u'modlgn_username')
        self.type(text=username, id=u'modlgn_username')
        self.type(text=password, id=u'modlgn_passwd')
        self.click(name=u'Submit')
        self.waits.forPageLoad(timeout=u'6000')
        self.asserts.assertValue(validator=u'Log out', name=u'Submit')

    def gotoHome(self):
        self.open(url=self._home)
        self.waits.forPageLoad(timeout=u'8000')

    def exit(self):
        self.closeWindow()
