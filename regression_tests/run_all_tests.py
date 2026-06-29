import unittest
import sys

from tests.test_registration import TestProductRegistrationAndScheduling
from tests.test_parameters import TestParameterRulesAndBoundaries
from tests.test_views_actions import TestViewsAndDataActions


def suite():
    loader = unittest.TestLoader()
    suite = unittest.TestSuite()

    suite.addTests(loader.loadTestsFromTestCase(
        TestProductRegistrationAndScheduling
    ))

    suite.addTests(loader.loadTestsFromTestCase(
        TestParameterRulesAndBoundaries
    ))

    suite.addTests(loader.loadTestsFromTestCase(
        TestViewsAndDataActions
    ))
    return suite


if __name__ == "__main__":
    print("\n" + "="*60)
    print("REGRESSION TEST SUITE - STABILITYLOG LIMS")
    print("="*60)

    runner = unittest.TextTestRunner(
        verbosity=2,
        failfast=False
    )

    result = runner.run(suite())

    print("\n" + "="*60)
    print(
        f"TOTAL : {result.testsRun} | "
        f"FAILED : {len(result.failures)} | "
        f"ERROR : {len(result.errors)}"
    )
    print("="*60)

    sys.exit(0 if result.wasSuccessful() else 1)