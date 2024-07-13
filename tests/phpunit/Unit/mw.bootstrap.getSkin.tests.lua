--[[
	Tests for mw.bootstrap.getSkin module

	@since 1.1

	@licence GNU GPL v3+
	@author Tobias Oetterer
]]

local testframework = require 'Module:TestFramework'

-- Tests
local tests = {
	{
		name = 'mw.bootstrap.parse.getSkin.lua: getSkin',
		func = mw.bootstrap.getSkin,
		args = { nil },
		expect = { 'vector-2022' }
	},
}

return testframework.getTestProvider( tests )
